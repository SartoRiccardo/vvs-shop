<?php

namespace Webkul\PosteShipping\Helpers;

use Webkul\PosteShipping\Models\PosteRate;

class SchemaShipping
{
    /**
     * Cheapest active rate for a destination country and weight, for
     * shippingDetails structured data. Same lookup as
     * PosteItaliane::calculate() minus the cart: destination country and
     * weight in, one rate out.
     *
     * @return array{price: float, service: string}|null Null when the
     *                                                   country has no zone or no band fits the weight.
     */
    public function getCheapestRate(string $countryCode, float $weightKg): ?array
    {
        $row = PosteRate::query()
            ->select('poste_rates.max_weight_kg', 'poste_rates.cost_eur', 'poste_services.id as service_id', 'poste_services.name as service_name')
            ->join('poste_zones', 'poste_zones.id', '=', 'poste_rates.zone_id')
            ->join('poste_services', 'poste_services.id', '=', 'poste_zones.service_id')
            ->join('poste_country_zones', 'poste_country_zones.zone_id', '=', 'poste_zones.id')
            ->where('poste_country_zones.country_code', $countryCode)
            ->where('poste_services.active', true)
            ->where('poste_rates.max_weight_kg', '>=', $weightKg)
            ->orderBy('poste_services.id')
            ->orderBy('poste_rates.max_weight_kg')
            ->get()
            // Collection::unique(), not SQL DISTINCT ON — relies on
            // orderBy(max_weight_kg) above to make the first occurrence per
            // service the cheapest applicable band.
            ->unique('service_id')
            ->sortBy('cost_eur')
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'price' => core()->convertPrice(core()->convertToBasePrice((float) $row->cost_eur, 'EUR')),
            'service' => $row->service_name,
        ];
    }
}
