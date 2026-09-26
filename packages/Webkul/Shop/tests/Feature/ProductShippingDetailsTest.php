<?php

use Illuminate\Support\Facades\DB;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;

function productOffers(string $html): array
{
    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

    return json_decode($matches[1], true)['offers'];
}

function cheapestRateCostEur(string $country, float $weightKg)
{
    return DB::table('poste_rates')
        ->join('poste_zones', 'poste_zones.id', '=', 'poste_rates.zone_id')
        ->join('poste_services', 'poste_services.id', '=', 'poste_zones.service_id')
        ->join('poste_country_zones', 'poste_country_zones.zone_id', '=', 'poste_zones.id')
        ->where('poste_country_zones.country_code', $country)
        ->where('poste_services.active', 1)
        ->where('poste_rates.max_weight_kg', '>=', $weightKg)
        ->orderBy('poste_rates.cost_eur')
        ->value('cost_eur');
}

it('emits geo-matched shipping details with the real cheapest rate and omits them when uncovered', function () {
    // Arrange
    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    /**
     * The faker weighs products up to 100 kg, which no shipping band fits;
     * pin a plushie-sized weight so a rate always resolves.
     */
    DB::table('product_attribute_values')
        ->where('product_id', $product->id)
        ->where('attribute_id', DB::table('attributes')->where('code', 'weight')->value('id'))
        ->update(['text_value' => 0.3]);

    $url = route('shop.product_or_category.index', $product->url_key).'?currency=USD';

    $weight = 0.4;

    $expectedValue = fn (string $country) => round(core()->convertPrice(
        core()->convertToBasePrice((float) cheapestRateCostEur($country, $weight), 'EUR')
    ), 2);

    // Act and Assert: the geo header picks the destination and its real rate.
    $offers = productOffers(get($url, ['CF-IPCountry' => 'DE'])->getContent());

    expect($offers['shippingDetails']['shippingDestination']['addressCountry'])->toBe('DE')
        ->and($offers['shippingDetails']['shippingRate']['currency'])->toBe('USD')
        ->and($offers['shippingDetails']['shippingRate']['value'])->toEqual($expectedValue('DE'))
        // Delivery time is config-gated and unset, so it is not emitted.
        ->and($offers['shippingDetails'])->not->toHaveKey('deliveryTime');

    // Act and Assert: another country re-renders (cache is keyed by country).
    $offers = productOffers(get($url, ['CF-IPCountry' => 'IT'])->getContent());

    expect($offers['shippingDetails']['shippingDestination']['addressCountry'])->toBe('IT')
        ->and($offers['shippingDetails']['shippingRate']['value'])->toEqual($expectedValue('IT'));

    // Act and Assert: a country with no rates emits nothing.
    $offers = productOffers(get($url, ['CF-IPCountry' => 'ZZ'])->getContent());

    expect($offers)->not->toHaveKey('shippingDetails');

    // Act and Assert: no geo header falls back to the configured default.
    $offers = productOffers(get($url)->getContent());

    expect($offers['shippingDetails']['shippingDestination']['addressCountry'])->toBe('US');
});
