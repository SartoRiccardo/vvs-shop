<?php

namespace Webkul\PosteShipping\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Webkul\Core\Models\Country;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;

class ZoneController extends Controller
{
    public function index(PosteService $service): View
    {
        $service->load('zones.countryZones', 'zones.rates');

        return view('poste_shipping::zones.index', compact('service'));
    }

    public function create(PosteService $service): View
    {
        $countries = Country::orderBy('name')->get();

        return view('poste_shipping::zones.create', compact('service', 'countries'));
    }

    public function store(Request $request, PosteService $service): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:255',
            'country_codes' => 'array',
            'country_codes.*' => 'string|size:2',
        ]);

        $zone = $service->zones()->create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        foreach ($data['country_codes'] ?? [] as $code) {
            $zone->countryZones()->create(['country_code' => $code]);
        }

        session()->flash('success', 'Zone created.');

        return redirect()->route('services.zones.index', $service);
    }

    public function edit(PosteService $service, PosteZone $zone): View
    {
        $countries = Country::orderBy('name')->get();
        $assignedCodes = $zone->countryZones->pluck('country_code')->toArray();

        return view('poste_shipping::zones.edit', compact('service', 'zone', 'countries', 'assignedCodes'));
    }

    public function update(Request $request, PosteService $service, PosteZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:255',
            'country_codes'   => 'array',
            'country_codes.*' => 'string|size:2',
        ]);

        $zone->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $zone->countryZones()->delete();

        foreach ($data['country_codes'] ?? [] as $code) {
            $zone->countryZones()->create(['country_code' => $code]);
        }

        session()->flash('success', 'Zone updated.');

        return redirect()->route('services.zones.index', $service);
    }

    public function destroy(PosteService $service, PosteZone $zone): RedirectResponse
    {
        $zone->delete();

        session()->flash('success', 'Zone deleted.');

        return redirect()->route('services.zones.index', $service);
    }
}
