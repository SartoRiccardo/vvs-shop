<?php

use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Checkout\Models\CartItem;
use Webkul\Core\Models\CoreConfig;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\FedExShipping\Carriers\FedExFICP;
use Webkul\FedExShipping\Models\FedExDemandCountry;
use Webkul\FedExShipping\Models\FedExDemandGroup;
use Webkul\FedExShipping\Models\FedExRate;
use Webkul\FedExShipping\Models\FedExSetting;
use Webkul\FedExShipping\Models\FedExZone;

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

/**
 * Seed the three FedExSetting rows (fuel, eu_discount, vat).
 */
function seedFedExSettings(float $fuelRate = 0.48, float $euDiscount = 0.50, float $vatRate = 22.0): void
{
    FedExSetting::insert([
        ['key' => 'fuel_surcharge_rate', 'value' => (string) $fuelRate],
        ['key' => 'eu_fuel_discount',    'value' => (string) $euDiscount],
        ['key' => 'vat_rate',            'value' => (string) $vatRate],
    ]);
}

/**
 * Create a zone row and a simple demand group, then return the zone.
 */
function createFedExZone(
    string $country,
    string $zoneCode,
    bool $isEuropeanZone = false,
    bool $isEu = false
): FedExZone {
    return FedExZone::create([
        'country_name'      => 'TestCountry',
        'country_code'      => $country,
        'zone_code'         => $zoneCode,
        'is_european_zone'  => $isEuropeanZone,
        'is_eu'             => $isEu,
    ]);
}

/**
 * Create a demand group.  Pass $country to also create a demand_country row.
 */
function createFedExDemandGroup(
    string $name,
    float $baseRate,
    float $perKgRate,
    ?string $country = null
): FedExDemandGroup {
    $group = FedExDemandGroup::create([
        'group_name'  => $name,
        'base_rate'   => $baseRate,
        'per_kg_rate' => $perKgRate,
    ]);

    if ($country) {
        FedExDemandCountry::create(['country_code' => $country, 'group_id' => $group->id]);
    }

    return $group;
}

/**
 * Create rate rows for a zone.  $brackets = ['2.0' => 10.00, ...].
 * Pass a tail row as ['NULL' => [flat, perKg]].
 */
function createFedExRates(string $zoneCode, array $brackets): void
{
    foreach ($brackets as $weight => $value) {
        if ($weight === 'NULL') {
            FedExRate::create([
                'zone_code'    => $zoneCode,
                'weight_max'   => null,
                'flat_rate'    => $value[0],
                'per_kg_rate'  => $value[1],
            ]);
        } else {
            FedExRate::create([
                'zone_code'    => $zoneCode,
                'weight_max'   => (float) $weight,
                'flat_rate'    => $value,
                'per_kg_rate'  => 0,
            ]);
        }
    }
}

/**
 * Build a cart with a shipping address and bind it to the cart session.
 * Creates a minimal real product to satisfy the cart_items.product_id FK.
 */
function makeFedExCart(string $country, float $itemWeightKg, int $qty = 1): Cart
{
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $cart = Cart::factory()->create();

    CartItem::factory()->create([
        'cart_id'           => $cart->id,
        'product_id'        => $product->id,
        'sku'               => $product->sku,
        'name'              => $product->name,
        'quantity'          => $qty,
        'weight'            => $itemWeightKg,
        'total_weight'      => $itemWeightKg * $qty,
        'base_total_weight' => $itemWeightKg * $qty,
        'price'             => 10.00,
        'base_price'        => 10.00,
        'total'             => 10.00 * $qty,
        'base_total'        => 10.00 * $qty,
        'type'              => $product->type,
    ]);

    CartAddress::factory()->create([
        'cart_id'      => $cart->id,
        'address_type' => CartAddress::ADDRESS_TYPE_SHIPPING,
        'country'      => $country,
    ]);

    cart()->setCart($cart);

    return $cart;
}

/**
 * Compute the expected EUR total for a given billable weight in a given zone scenario.
 *
 * @param  float   $baseRate        from rate table
 * @param  float   $demandBase      group base_rate
 * @param  float   $demandPerKg     group per_kg_rate
 * @param  float   $billableWeight
 * @param  bool    $isEuropeanZone  halves the fuel surcharge
 * @param  bool    $isEu            adds 22% VAT
 * @param  float   $fuelRate        defaults to 0.48
 * @param  float   $euDiscount      defaults to 0.50
 * @param  float   $vatRate         defaults to 22.0
 */
function expectedFedExEur(
    float $baseRate,
    float $demandBase,
    float $demandPerKg,
    float $billableWeight,
    bool $isEuropeanZone,
    bool $isEu,
    float $fuelRate = 0.48,
    float $euDiscount = 0.50,
    float $vatRate = 22.0
): float {
    $demandSurcharge = max($demandBase, $billableWeight * $demandPerKg);
    $effectiveFuel   = $isEuropeanZone ? $fuelRate * (1 - $euDiscount) : $fuelRate;
    $fuelSurcharge   = ($baseRate + $demandSurcharge) * $effectiveFuel;
    $subtotal        = $baseRate + $demandSurcharge + $fuelSurcharge;
    $vat             = $isEu ? $subtotal * ($vatRate / 100) : 0.0;

    return $subtotal + $vat;
}

// -------------------------------------------------------------------------
// Test isolation
// -------------------------------------------------------------------------

// Wipe all FedEx rate/zone/setting data before each test so seeded data from
// FedExFICPSeeder does not interfere with our controlled test scenarios.
// DatabaseTransactions rolls back the deletions (and new inserts) automatically.
beforeEach(function () {
    FedExDemandCountry::query()->delete();
    FedExDemandGroup::query()->delete();
    FedExRate::query()->delete();
    FedExZone::query()->delete();
    FedExSetting::query()->delete();
});

// Volumetric weight with the hardcoded 15×20×30 cm box: (15*20*30)/5000 = 1.8 kg.
// Any cart weight ≤ 1.8 → billable = ceil(1.8*2)/2 = 2.0.
// Any cart weight  > 1.8 → billable = ceil(actual*2)/2.
const VOLUMETRIC_KG = 1.8;

// -------------------------------------------------------------------------
// Tests
// -------------------------------------------------------------------------

it('returns false when the fedex_ficp carrier is explicitly disabled', function () {
    CoreConfig::factory()->create([
        'code'         => 'sales.carriers.fedex_ficp.active',
        'value'        => '0',
        'channel_code' => 'default',
    ]);

    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00]);
    createFedExDemandGroup('default', 0.90, 0.70);
    makeFedExCart('DE', 0.3);

    expect((new FedExFICP)->calculate())->toBeFalse();
});

it('returns false when the cart has no shipping address', function () {
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00]);
    createFedExDemandGroup('default', 0.90, 0.70);

    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $cart    = Cart::factory()->create();
    CartItem::factory()->create([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'name'       => $product->name,
        'quantity'   => 1,
        'weight'     => 0.3,
        'type'       => $product->type,
    ]);
    cart()->setCart($cart);

    expect((new FedExFICP)->calculate())->toBeFalse();
});

it('returns false when the destination country is not in the zones table', function () {
    seedFedExSettings();
    createFedExDemandGroup('default', 0.90, 0.70);
    // No zone created for 'ZZ'.
    makeFedExCart('ZZ', 0.3);

    expect((new FedExFICP)->calculate())->toBeFalse();
});

it('returns false when no rate row exists for the zone', function () {
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    // No rate rows created for zone R.
    createFedExDemandGroup('default', 0.90, 0.70);
    makeFedExCart('DE', 0.3);

    expect((new FedExFICP)->calculate())->toBeFalse();
});

it('calculates the correct price for an EU destination with intra-europe demand', function () {
    // Germany: zone R, is_european_zone=true, is_eu=true, intra_europe demand (0+0).
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00]);                    // billable weight = 2.0
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'DE');   // 0 demand surcharge
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('DE', 0.3); // cart weight = 0.4 → billable = 2.0 (volumetric dominates)

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $expectedEur  = expectedFedExEur(10.00, 0.00, 0.00, 2.0, true, true);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toBeGreaterThan(0.0);
    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
    expect($rates[0]->carrier)->toBe('fedex_ficp');
    expect($rates[0]->method)->toBe('fedex_ficp_ficp');
});

it('calculates the correct price for a non-EU destination without VAT', function () {
    // USA: zone H, not european zone, not EU, default demand group.
    seedFedExSettings();
    createFedExZone('US', 'H', false, false);
    createFedExRates('H', ['2.0' => 20.00]);
    createFedExDemandGroup('default', 0.90, 0.70);
    // No demand country row for US → falls back to default group.

    makeFedExCart('US', 0.3); // billable = 2.0

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    // demand = max(0.90, 2.0 * 0.70) = max(0.90, 1.40) = 1.40
    $expectedEur  = expectedFedExEur(20.00, 0.90, 0.70, 2.0, false, false);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('applies reduced fuel surcharge for european zone destinations', function () {
    // UK: is_european_zone=true, is_eu=false → 24% fuel, no VAT.
    seedFedExSettings();
    createFedExZone('GB', 'S', true, false);
    createFedExRates('S', ['2.0' => 12.00]);
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'GB');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('GB', 0.3); // billable = 2.0

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $expectedEur  = expectedFedExEur(12.00, 0.00, 0.00, 2.0, true, false);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('uses volumetric weight when it exceeds actual cart weight', function () {
    // Item = 0.3 kg → cart = 0.4 → volumetric = 1.8 → billable = 2.0.
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00, '3.0' => 15.00]);
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'DE');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('DE', 0.3);

    $rates = (new FedExFICP)->calculate();

    // Should use the 2.0 bracket (flat_rate = 10), not 3.0.
    $expectedEur  = expectedFedExEur(10.00, 0.00, 0.00, 2.0, true, true);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('uses actual weight when it exceeds the volumetric weight', function () {
    // Item = 2.0 kg → cart = 2.1 → max(2.1, 1.8) = 2.1 → billable = ceil(2.1*2)/2 = 2.5.
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00, '2.5' => 13.00, '3.0' => 16.00]);
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'DE');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('DE', 2.0); // cart = 2.1, billable = 2.5

    $rates = (new FedExFICP)->calculate();

    $expectedEur  = expectedFedExEur(13.00, 0.00, 0.00, 2.5, true, true);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('rounds billable weight up to the nearest 0.5 kg', function () {
    // Item = 2.1 kg → cart = 2.2 → max(2.2, 1.8) = 2.2 → ceil(2.2*2)/2 = ceil(4.4)/2 = 2.5.
    seedFedExSettings();
    createFedExZone('DE', 'R', true, true);
    createFedExRates('R', ['2.0' => 10.00, '2.5' => 13.00]);
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'DE');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('DE', 2.1); // cart = 2.2, billable = 2.5

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $expectedEur  = expectedFedExEur(13.00, 0.00, 0.00, 2.5, true, true);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('interpolates using the tail row when billable weight exceeds all explicit brackets', function () {
    // Brackets: 2.0 @ €10, 3.0 @ €14, tail: flat=14, per_kg=2.50.
    // Item = 3.5 kg → cart = 3.6 → billable = ceil(3.6*2)/2 = 4.0.
    // No bracket ≥ 4.0 → tail row: baseRate = 14 + (4.0 - 3.0) * 2.50 = 16.50.
    seedFedExSettings();
    createFedExZone('US', 'H', false, false);
    createFedExRates('H', [
        '2.0'  => 10.00,
        '3.0'  => 14.00,
        'NULL' => [14.00, 2.50],
    ]);
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('US', 3.5); // billable = 4.0

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $baseRate     = 14.00 + (4.0 - 3.0) * 2.50; // = 16.50
    $expectedEur  = expectedFedExEur($baseRate, 0.90, 0.70, 4.0, false, false);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('uses the specific demand group for a mapped country over the default', function () {
    // Israel has its own demand group (base=0.90, per_kg=1.00).
    seedFedExSettings();
    createFedExZone('IL', 'V', true, false); // European zone but not EU
    createFedExRates('V', ['2.0' => 11.00]);
    createFedExDemandGroup('israel', 0.90, 1.00, 'IL');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('IL', 0.3); // billable = 2.0

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    // demand = max(0.90, 2.0 * 1.00) = max(0.90, 2.00) = 2.00
    $expectedEur  = expectedFedExEur(11.00, 0.90, 1.00, 2.0, true, false);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('falls back to the default demand group for an unmapped country', function () {
    // Australia has no explicit demand country row → falls back to default (0.90, 0.70).
    seedFedExSettings();
    createFedExZone('AU', 'B', false, false);
    createFedExRates('B', ['2.0' => 22.00]);
    createFedExDemandGroup('oceania', 0.90, 0.09, 'NZ'); // NZ mapped, not AU
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('AU', 0.3); // billable = 2.0

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $expectedEur  = expectedFedExEur(22.00, 0.90, 0.70, 2.0, false, false);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});

it('applies zero demand surcharge when demand group rates are both zero', function () {
    seedFedExSettings();
    createFedExZone('FR', 'T', true, true);
    createFedExRates('T', ['2.0' => 10.00]);
    createFedExDemandGroup('intra_europe', 0.00, 0.00, 'FR');
    createFedExDemandGroup('default', 0.90, 0.70);

    makeFedExCart('FR', 0.3);

    $rates = (new FedExFICP)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    // demand = max(0, 0) = 0 → only fuel + VAT on base rate
    $expectedEur  = expectedFedExEur(10.00, 0.00, 0.00, 2.0, true, true);
    $expectedBase = core()->convertToBasePrice($expectedEur, 'EUR');

    expect($rates[0]->base_price)->toEqualWithDelta($expectedBase, 0.001);
});
