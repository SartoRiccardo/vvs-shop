<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Contracts\ProductCurrencyPrice;

class ProductCurrencyPriceRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model(): string
    {
        return ProductCurrencyPrice::class;
    }

    /**
     * Save the per-currency price overrides submitted from the admin form.
     *
     * Expects a flat `currency_prices[currency_id] => amount` array; empty
     * amounts delete the override so the currency falls back to conversion.
     *
     * @param  Product  $product
     * @return void
     */
    public function saveCurrencyPrices(array $data, $product)
    {
        if (! isset($data['currency_prices']) || ! is_array($data['currency_prices'])) {
            return;
        }

        $baseCurrencyId = core()->getCurrentChannel()->base_currency_id;

        foreach ($data['currency_prices'] as $currencyId => $amount) {
            if ($currencyId == $baseCurrencyId) {
                continue;
            }

            $existing = $product->currency_prices()->where('currency_id', $currencyId)->first();

            if ($amount === null || $amount === '' || ! is_numeric($amount)) {
                $existing?->delete();

                continue;
            }

            $existing
                ? $existing->update(['amount' => $amount])
                : $product->currency_prices()->create(['currency_id' => $currencyId, 'amount' => $amount]);
        }
    }
}
