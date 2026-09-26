<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Core\Models\CurrencyProxy;
use Webkul\Product\Contracts\ProductCurrencyPrice as ProductCurrencyPriceContract;
use Webkul\Product\Database\Factories\ProductCurrencyPriceFactory;

class ProductCurrencyPrice extends Model implements ProductCurrencyPriceContract
{
    use HasFactory;

    /**
     * Add fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'amount',
        'product_id',
        'currency_id',
    ];

    /**
     * Get the product that owns the currency price.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Get the currency that owns the currency price.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(CurrencyProxy::modelClass());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ProductCurrencyPriceFactory::new();
    }
}
