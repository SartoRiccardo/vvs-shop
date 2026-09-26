<?php

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\Currency;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Models\ProductCurrencyPrice;

use function Pest\Laravel\get;
use function Pest\Laravel\putJson;

/**
 * Attach EUR to the current channel; returns [currency, exchange rate].
 */
function attachEurToChannel(): Currency
{
    $eur = Currency::firstWhere('code', 'EUR')
        ?? Currency::factory()->create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
        ]);

    $channel = core()->getCurrentChannel();

    $channel->currencies()->sync(
        $channel->currencies->pluck('id')->merge([$eur->id])->unique()->toArray()
    );

    if (! DB::table('currency_exchange_rates')->where('target_currency', $eur->id)->exists()) {
        DB::table('currency_exchange_rates')->insert([
            'target_currency' => $eur->id,
            'rate' => 0.5,
        ]);
    }

    return $eur;
}

function productUpdatePayload($product): array
{
    return [
        'sku' => $product->sku,
        'url_key' => $product->url_key,
        'short_description' => fake()->sentence(),
        'description' => fake()->paragraph(),
        'name' => fake()->words(3, true),
        'price' => fake()->randomFloat(2, 1, 1000),
        'weight' => fake()->numberBetween(0, 100),
        'channel' => core()->getCurrentChannelCode(),
        'locale' => app()->getLocale(),
    ];
}

it('shows the currency prices section with a converted reference on the product edit page', function () {
    // Arrange
    $eur = attachEurToChannel();

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    // Act and Assert
    $this->loginAsAdmin();

    get(route('admin.catalog.products.edit', $product->id))
        ->assertOk()
        ->assertSee(trans('admin::app.catalog.products.edit.price.currency.title'))
        ->assertSee('currency_prices['.$eur->id.']')
        ->assertSee(trans('admin::app.catalog.products.edit.price.currency.converts-to', ['price' => '']));
});

it('saves and clears a per-currency price override when updating a product', function () {
    // Arrange
    $eur = attachEurToChannel();

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $this->loginAsAdmin();

    // Act and Assert: saving an amount persists the override.
    putJson(route('admin.catalog.products.update', $product->id), array_merge(productUpdatePayload($product), [
        'currency_prices' => [$eur->id => '35'],
    ]))->assertRedirect(route('admin.catalog.products.index'));

    expect(ProductCurrencyPrice::query()
        ->where('product_id', $product->id)
        ->where('currency_id', $eur->id)
        ->value('amount'))->toEqual('35.0000');

    // Act and Assert: clearing the amount removes the override.
    putJson(route('admin.catalog.products.update', $product->id), array_merge(productUpdatePayload($product), [
        'currency_prices' => [$eur->id => ''],
    ]))->assertRedirect(route('admin.catalog.products.index'));

    expect(ProductCurrencyPrice::query()
        ->where('product_id', $product->id)
        ->where('currency_id', $eur->id)
        ->exists())->toBeFalse();
});
