<?php

use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Checkout\Models\CartItem;
use Webkul\Core\Models\CoreConfig;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\PosteShipping\Carriers\PosteItaliane;
use Webkul\PosteShipping\Models\PosteCountryZone;
use Webkul\PosteShipping\Models\PosteRate;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;

// -------------------------------------------------------------------------
// Test isolation
// -------------------------------------------------------------------------

// Deactivate all seeded PosteServices and the carrier config before each test so
// pre-existing DB data does not interfere with our carefully controlled scenarios.
beforeEach(function () {
    PosteService::query()->update(['active' => false]);

    CoreConfig::updateOrCreate(
        ['code' => 'sales.carriers.poste_italiane.active', 'channel_code' => 'default'],
        ['value' => '0']
    );
});

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

/**
 * Activate the PosteItaliane carrier (override any pre-existing config row).
 */
function activatePoste(): void
{
    CoreConfig::updateOrCreate(
        ['code' => 'sales.carriers.poste_italiane.active', 'channel_code' => 'default'],
        ['value' => '1']
    );
}

/**
 * Create a service → zone → country + rate rows for a given country.
 *
 * @param  string  $serviceName
 * @param  bool    $serviceActive
 * @param  string  $country       ISO-2 code
 * @param  array   $rates         ['0.5000' => 6.00, ...]
 */
function createPosteService(
    string $serviceName,
    bool $serviceActive,
    string $country,
    array $rates,
    string $description = ''
): PosteService {
    $service = PosteService::create([
        'name'        => $serviceName,
        'description' => $description,
        'active'      => $serviceActive,
    ]);

    $zone = PosteZone::create([
        'service_id'  => $service->id,
        'name'        => 'TestZone',
        'description' => '',
    ]);

    PosteCountryZone::create(['zone_id' => $zone->id, 'country_code' => $country]);

    foreach ($rates as $maxWeight => $cost) {
        PosteRate::create([
            'zone_id'       => $zone->id,
            'max_weight_kg' => $maxWeight,
            'cost_eur'      => $cost,
        ]);
    }

    return $service;
}

/**
 * Build a cart with one item and a shipping address, then bind it to the cart session.
 * Creates a minimal real product to satisfy the cart_items.product_id FK.
 */
function makePosteCart(string $country, float $itemWeightKg, int $qty = 1): Cart
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

// -------------------------------------------------------------------------
// Tests
// -------------------------------------------------------------------------

it('returns false when the poste_italiane carrier is inactive', function () {
    // No active config → isAvailable() returns false.
    createPosteService('Svc', true, 'IT', ['0.5000' => 6.00]);
    makePosteCart('IT', 0.3);

    $result = (new PosteItaliane)->calculate();

    expect($result)->toBeFalse();
});

it('returns false when the cart has no shipping address', function () {
    activatePoste();
    createPosteService('Svc', true, 'IT', ['0.5000' => 6.00]);

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

    expect((new PosteItaliane)->calculate())->toBeFalse();
});

it('returns false when the destination country is not covered by any zone', function () {
    activatePoste();
    // Only covers IT, not DE.
    createPosteService('Svc', true, 'IT', ['0.5000' => 6.00]);
    makePosteCart('DE', 0.3);

    expect((new PosteItaliane)->calculate())->toBeFalse();
});

it('returns the correct rate for a covered country and weight', function () {
    activatePoste();
    createPosteService('PosteItaliane', true, 'IT', [
        '0.5000' => 6.00,
        '1.0000' => 8.00,
        '2.0000' => 12.00,
    ]);

    // Cart weight = 0.3 + 0.1 offset = 0.4 kg → cheapest band ≥ 0.4 is 0.5 kg → €6.
    makePosteCart('IT', 0.3);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);

    $expectedBase = core()->convertToBasePrice(6.00, 'EUR');
    expect($rates[0]->base_price)->toEqual($expectedBase);
    expect($rates[0]->carrier)->toBe('poste_italiane');
});

it('returns the cheapest applicable band, not a smaller band than the cart weight', function () {
    activatePoste();
    createPosteService('Svc', true, 'IT', [
        '0.5000' => 6.00,
        '1.0000' => 8.00,
        '2.0000' => 12.00,
    ]);

    // Cart weight = 0.5 + 0.1 = 0.6 kg → smallest band ≥ 0.6 is 1.0 kg → €8.
    makePosteCart('IT', 0.5);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);
    $expectedBase = core()->convertToBasePrice(8.00, 'EUR');
    expect($rates[0]->base_price)->toEqual($expectedBase);
});

it('returns false when cart weight exceeds all available rate bands', function () {
    activatePoste();
    createPosteService('Svc', true, 'IT', ['0.5000' => 6.00]);

    // Cart weight = 0.5 + 0.1 = 0.6 kg > max band 0.5 → no applicable rate.
    makePosteCart('IT', 0.5);

    expect((new PosteItaliane)->calculate())->toBeFalse();
});

it('returns false when the matching service is inactive', function () {
    activatePoste();
    createPosteService('Svc', false, 'IT', ['1.0000' => 8.00]);
    makePosteCart('IT', 0.3);

    expect((new PosteItaliane)->calculate())->toBeFalse();
});

it('returns one rate per active service when multiple services cover the same country', function () {
    activatePoste();
    createPosteService('ServiceA', true, 'IT', ['1.0000' => 8.00]);
    createPosteService('ServiceB', true, 'IT', ['1.0000' => 10.00]);
    makePosteCart('IT', 0.3);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(2);
    $methods = array_column($rates, 'method_title');
    expect($methods)->toContain('ServiceA')->toContain('ServiceB');
});

it('skips an inactive service but still returns active ones', function () {
    activatePoste();
    createPosteService('Active', true, 'IT', ['1.0000' => 8.00]);
    createPosteService('Inactive', false, 'IT', ['1.0000' => 7.00]);
    makePosteCart('IT', 0.3);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);
    expect($rates[0]->method_title)->toBe('Active');
});

it('includes the 0.1 kg handling offset in the weight lookup', function () {
    activatePoste();
    createPosteService('Svc', true, 'IT', [
        '0.0900' => 5.00,  // too small: 0.09 < 0.1 offset alone
        '0.5000' => 6.00,
    ]);

    // Item weight = 0 → cart weight = 0.0 + 0.1 = 0.1 kg.
    // Band 0.09 is not ≥ 0.1, so must use the 0.5 band.
    makePosteCart('IT', 0.0);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);
    $expectedBase = core()->convertToBasePrice(6.00, 'EUR');
    expect($rates[0]->base_price)->toEqual($expectedBase);
});

it('returns correct carrier and method fields on the rate object', function () {
    activatePoste();
    $service = createPosteService('PostMiniBox Test', true, 'FR', ['1.0000' => 9.00]);
    makePosteCart('FR', 0.3);

    $rates = (new PosteItaliane)->calculate();

    expect($rates)->toBeArray()->toHaveCount(1);
    expect($rates[0]->carrier)->toBe('poste_italiane');
    expect($rates[0]->carrier_title)->toBe('Poste Italiane');
    expect($rates[0]->method)->toBe('poste_italiane_'.$service->id);
    expect($rates[0]->method_title)->toBe('PostMiniBox Test');
});
