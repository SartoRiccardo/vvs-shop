<?php

namespace Webkul\PosteShipping\Carriers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\PosteShipping\Models\PosteRate;
use Webkul\Shipping\Carriers\AbstractShipping;

class PosteItaliane extends AbstractShipping
{
    protected $code = 'poste_italiane';

    /**
     * Returns one CartShippingRate per active service that covers the
     * destination country and fits within a weight band.
     *
     * @return CartShippingRate[]|false
     */
    public function calculate(): array|false
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $cart = Cart::getCart();
        $address = $cart->shipping_address;

        if (! $address) {
            return false;
        }

        $countryCode = $address->country;
        $cartWeightKg = $cart->items->sum(fn ($item) => (float) $item->weight * $item->quantity) + 0.1;

        // One query: join through country_zones → zones → services, filter by
        // country + weight + active service, pick cheapest band per service.
        $matchingRates = PosteRate::query()
            ->select('poste_rates.*', 'poste_services.id as service_id', 'poste_services.name as service_name', 'poste_services.description as service_description')
            ->join('poste_zones', 'poste_zones.id', '=', 'poste_rates.zone_id')
            ->join('poste_services', 'poste_services.id', '=', 'poste_zones.service_id')
            ->join('poste_country_zones', 'poste_country_zones.zone_id', '=', 'poste_zones.id')
            ->where('poste_country_zones.country_code', $countryCode)
            ->where('poste_services.active', true)
            ->where('poste_rates.max_weight_kg', '>=', $cartWeightKg)
            ->orderBy('poste_services.id')
            ->orderBy('poste_rates.max_weight_kg')
            ->get()
            // Collection::unique(), not SQL DISTINCT ON — relies on orderBy(max_weight_kg) above
            // to ensure the first occurrence per service is the cheapest applicable band.
            ->unique('service_id');

        $rates = [];

        foreach ($matchingRates as $row) {
            $basePrice = core()->convertToBasePrice((float) $row->cost_eur, 'EUR');
            $displayPrice = core()->convertPrice($basePrice);

            $shippingRate = new CartShippingRate;
            $shippingRate->carrier = $this->code;
            $shippingRate->carrier_title = 'Poste Italiane';
            $shippingRate->method = $this->code.'_'.$row->service_id;
            $shippingRate->method_title = $row->service_name;
            $shippingRate->method_description = $row->service_description;
            $shippingRate->price = $displayPrice;
            $shippingRate->base_price = $basePrice;

            $rates[] = $shippingRate;
        }

        return empty($rates) ? false : $rates;
    }

    public function isAvailable(): bool
    {
        return (bool) $this->getConfigData('active');
    }

    public function getTitle(): string
    {
        return $this->getConfigData('title') ?? 'Poste Italiane';
    }

    public function getDescription(): string
    {
        return $this->getConfigData('description') ?? 'Poste Italiane';
    }

}
