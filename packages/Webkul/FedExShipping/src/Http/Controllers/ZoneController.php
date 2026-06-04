<?php

namespace Webkul\FedExShipping\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Webkul\Core\Models\Country;
use Webkul\FedExShipping\Models\FedExRate;
use Webkul\FedExShipping\Models\FedExZone;

class ZoneController extends Controller
{
    public function index(): View
    {
        // Aggregate zone data from fedex_ficp_zones, one row per country
        $zoneRows = DB::table('fedex_ficp_zones')
            ->select('zone_code', DB::raw('COUNT(*) as countries_count'), DB::raw('MAX(is_european_zone) as is_european_zone'))
            ->groupBy('zone_code')
            ->orderBy('zone_code')
            ->get();

        // For each zone, fetch countries and rates
        $zones = $zoneRows->map(function ($zoneData) {
            $zoneData->countries = DB::table('fedex_ficp_zones')
                ->where('zone_code', $zoneData->zone_code)
                ->orderBy('country_code')
                ->get();

            $zoneData->rates = FedExRate::where('zone_code', $zoneData->zone_code)
                ->orderByRaw('weight_max IS NULL')
                ->orderBy('weight_max')
                ->get();

            return $zoneData;
        });

        return view('fedex_shipping::zones.index', compact('zones'));
    }

    public function edit(string $zoneCode): View
    {
        $zone = FedExZone::where('zone_code', $zoneCode)->firstOrFail();
        $countries = Country::orderBy('name')->get();
        $assignedCodes = FedExZone::where('zone_code', $zoneCode)->pluck('country_code')->toArray();
        $rates = FedExRate::where('zone_code', $zoneCode)
            ->orderByRaw('weight_max IS NULL')
            ->orderBy('weight_max')
            ->get();

        return view('fedex_shipping::zones.edit', compact('zone', 'zoneCode', 'countries', 'assignedCodes', 'rates'));
    }

    public function update(Request $request, string $zoneCode): RedirectResponse
    {
        $data = $request->validate([
            'is_european_zone' => 'boolean',
            'country_codes'    => 'array',
            'country_codes.*'  => 'string|size:2',
        ]);

        $isEuropeanZone = (bool) ($data['is_european_zone'] ?? false);
        $newCodes = $data['country_codes'] ?? [];

        // Get previous country codes for this zone
        $previousCodes = FedExZone::where('zone_code', $zoneCode)->pluck('country_code')->toArray();

        // Update is_european_zone on all existing rows for this zone
        FedExZone::where('zone_code', $zoneCode)->update(['is_european_zone' => $isEuropeanZone]);

        // Delete rows for deselected countries
        $toRemove = array_diff($previousCodes, $newCodes);
        if (! empty($toRemove)) {
            FedExZone::where('zone_code', $zoneCode)->whereIn('country_code', $toRemove)->delete();
        }

        // Handle newly selected countries
        $toAdd = array_diff($newCodes, $previousCodes);
        foreach ($toAdd as $code) {
            $existing = FedExZone::where('country_code', $code)->first();
            if ($existing) {
                // Country exists in another zone — reassign it
                $existing->update([
                    'zone_code'        => $zoneCode,
                    'is_european_zone' => $isEuropeanZone,
                ]);
            } else {
                // Create a new row, looking up country name from the Country model
                $country = Country::where('code', $code)->first();
                FedExZone::create([
                    'country_name'     => $country ? $country->name : $code,
                    'country_code'     => $code,
                    'zone_code'        => $zoneCode,
                    'is_european_zone' => $isEuropeanZone,
                    'is_eu'            => false,
                ]);
            }
        }

        session()->flash('success', 'Zone updated.');

        return redirect()->route('fedex.zones.index');
    }
}
