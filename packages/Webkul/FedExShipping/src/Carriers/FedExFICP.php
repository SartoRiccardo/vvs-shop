<?php

namespace Webkul\FedExShipping\Carriers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\FedExShipping\Models\FedExDemandCountry;
use Webkul\FedExShipping\Models\FedExDemandGroup;
use Webkul\FedExShipping\Models\FedExRate;
use Webkul\FedExShipping\Models\FedExSetting;
use Webkul\FedExShipping\Models\FedExZone;
use Webkul\Shipping\Carriers\AbstractShipping;

class FedExFICP extends AbstractShipping
{
    protected $code = 'fedex_ficp';

    public function calculate(): array|false
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $cart = Cart::getCart();
        $address = $cart?->shipping_address;

        if (! $address) {
            return false;
        }

        $countryCode  = $address->country;
        $cartWeightKg = $cart->items->sum(fn ($item) => (float) $item->weight * $item->quantity) + 0.1;

        $settings = FedExSetting::all();

        // TODO: derive from cart item dimensions once products have dimension attributes.
        $lengthCm = 15;
        $widthCm  = 20;
        $heightCm = 30;
        $volumetricWeight = ($lengthCm * $widthCm * $heightCm) / 5000;

        $billableWeight = ceil(max($cartWeightKg, $volumetricWeight) * 2) / 2;

        $zone = FedExZone::where('country_code', $countryCode)->first();
        if (! $zone) {
            return false;
        }

        // Smallest bracket that fits; fall back to tail row (weight_max IS NULL).
        $rateRow = FedExRate::where('zone_code', $zone->zone_code)
            ->where(fn ($q) => $q->where('weight_max', '>=', $billableWeight)->orWhereNull('weight_max'))
            ->orderByRaw('weight_max IS NULL')
            ->orderBy('weight_max')
            ->first();

        if (! $rateRow) {
            return false;
        }

        if (is_null($rateRow->weight_max)) {
            $prevMax  = FedExRate::where('zone_code', $zone->zone_code)->whereNotNull('weight_max')->max('weight_max');
            $baseRate = $rateRow->flat_rate + ($billableWeight - (float) $prevMax) * $rateRow->per_kg_rate;
        } else {
            $baseRate = $rateRow->flat_rate;
        }

        $demandGroup = FedExDemandCountry::where('country_code', $countryCode)->first()?->group
            ?? FedExDemandGroup::where('group_name', 'default')->first();

        $demandSurcharge = max($demandGroup->base_rate, $billableWeight * $demandGroup->per_kg_rate);

        $fuelPct = (float) ($this->getConfigData('fuel_surcharge_percentage') ?? 49.25) / 100;
        if ($zone->is_european_zone) {
            $fuelPct *= 1 - (float) ($settings['eu_fuel_discount'] ?? 0.50);
        }
        $fuelSurcharge = ($baseRate + $demandSurcharge) * $fuelPct;

        $subtotal = $baseRate + $demandSurcharge + $fuelSurcharge;

        $vatAmount = 0;
        if ($zone->is_eu) {
            $vatAmount = $subtotal * ((float) ($settings['vat_rate'] ?? 22) / 100);
        }

        $totalEur     = $subtotal + $vatAmount;
        $basePrice    = core()->convertToBasePrice($totalEur, 'EUR');
        $displayPrice = core()->convertPrice($basePrice);

        $rate                     = new CartShippingRate;
        $rate->carrier            = $this->code;
        $rate->carrier_title      = $this->getTitle();
        $rate->method             = $this->code.'_ficp';
        $rate->method_title       = 'FedEx International Connect Plus';
        $rate->method_description = 'FedEx FICP — tracked international delivery';
        $rate->price              = $displayPrice;
        $rate->base_price         = $basePrice;

        return [$rate];
    }

    public function isAvailable(): bool
    {
        return (bool) ($this->getConfigData('active') ?? true);
    }

    public function getTitle(): string
    {
        return $this->getConfigData('title') ?? 'FedEx International Connect Plus';
    }

    public function getDescription(): string
    {
        return $this->getConfigData('description') ?? 'FedEx FICP';
    }
}
