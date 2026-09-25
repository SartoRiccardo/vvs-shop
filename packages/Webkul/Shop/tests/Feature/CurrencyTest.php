<?php

use Webkul\Core\Models\Currency;

use function Pest\Laravel\get;

/**
 * Enable EUR on the current channel so the geo fallback can pick it.
 */
function enableEurOnChannel(): void
{
    $eur = Currency::factory()->create([
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
    ]);

    $channel = core()->getCurrentChannel();

    $channel->currencies()->sync(
        $channel->currencies->pluck('id')->merge([$eur->id])->unique()->toArray()
    );
}

it('defaults to EUR for European countries based on the Cloudflare country header', function () {
    // Arrange
    enableEurOnChannel();

    // Act
    get(route('shop.home.index'), ['CF-IPCountry' => 'DE']);

    // Assert
    expect(core()->getCurrentCurrencyCode())->toBe('EUR');
});

it('defaults to USD for non-European countries based on the Cloudflare country header', function () {
    // Arrange
    enableEurOnChannel();

    // Act
    get(route('shop.home.index'), ['CF-IPCountry' => 'US']);

    // Assert
    expect(core()->getCurrentCurrencyCode())->toBe('USD');
});

it('falls back to the channel base currency when no country header is present', function () {
    // Arrange
    enableEurOnChannel();

    // Act
    get(route('shop.home.index'));

    // Assert
    expect(core()->getCurrentCurrencyCode())->toBe('USD');
});

it('lets an explicit currency query param override the geo default', function () {
    // Arrange
    enableEurOnChannel();

    // Act
    get(route('shop.home.index', ['currency' => 'USD']), ['CF-IPCountry' => 'DE']);

    // Assert
    expect(core()->getCurrentCurrencyCode())->toBe('USD');
});
