<?php

use Illuminate\Support\Facades\DB;
use Webkul\Checkout\Facades\Cart;
use Webkul\Core\Models\Currency;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Models\ProductCurrencyPrice;

/**
 * Single test on purpose: core() and repository singletons cache currency
 * models across tests while RefreshDatabase rolls the DB back, so id-based
 * assertions go stale between tests in the same process.
 */
it('applies per-currency price overrides to display and cart prices, converting when absent', function () {
    // Arrange: EUR on the channel at a 0.50 rate.
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

    /**
     * The suite runs against a live database (DatabaseTransactions), so the
     * existing rate may be anything — assert against it, not a constant.
     */
    $rate = (float) DB::table('currency_exchange_rates')
        ->where('target_currency', $eur->id)
        ->value('rate');

    core()->setCurrentCurrency('EUR');

    // A product without an override: rate conversion applies.
    $convertedProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    $convertedPrices = $convertedProduct->getTypeInstance()->getProductPrices();

    expect($convertedPrices['final']['price'])
        ->toEqual($convertedProduct->getTypeInstance()->getMinimalPrice() * $rate);

    // A product with an EUR override: the exact amount wins.
    $overriddenProduct = (new ProductFaker)->getSimpleProductFactory()->create();

    ProductCurrencyPrice::create([
        'product_id' => $overriddenProduct->id,
        'currency_id' => $eur->id,
        'amount' => 35,
    ]);

    $overriddenPrices = $overriddenProduct->getTypeInstance()->getProductPrices();

    expect($overriddenPrices['final']['price'])->toBe(35.0)
        ->and($overriddenPrices['final']['formatted_price'])->toContain('35')
        // Regular price still shows the auto-converted base price.
        ->and($overriddenPrices['regular']['price'])
        ->toEqual((float) $overriddenProduct->price * $rate);

    // The cart charges the override per unit and keeps the base price intact.
    Cart::addProduct($overriddenProduct, [
        'product_id' => $overriddenProduct->id,
        'quantity' => 2,
    ]);

    $item = Cart::getCart()->items->first();

    expect($item->price)->toEqual(35.0)
        ->and($item->total)->toEqual(70.0)
        ->and($item->base_price)->toEqual((float) $overriddenProduct->getTypeInstance()->getMinimalPrice());
});
