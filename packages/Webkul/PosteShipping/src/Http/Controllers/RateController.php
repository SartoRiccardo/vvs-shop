<?php

namespace Webkul\PosteShipping\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;
use Webkul\PosteShipping\Models\PosteRate;

class RateController extends Controller
{
    public function create(PosteService $service, PosteZone $zone): \Illuminate\View\View
    {
        return view('poste_shipping::rates.create', compact('service', 'zone'));
    }

    public function store(Request $request, PosteService $service, PosteZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'max_weight_kg' => 'required|numeric|min:0.001',
            'cost_eur'      => 'required|numeric|min:0',
        ]);

        $zone->rates()->create($data);

        session()->flash('success', 'Rate added.');

        return redirect()->route('services.zones.index', $service);
    }

    public function edit(PosteService $service, PosteZone $zone, PosteRate $rate): \Illuminate\View\View
    {
        return view('poste_shipping::rates.edit', compact('service', 'zone', 'rate'));
    }

    public function update(Request $request, PosteService $service, PosteZone $zone, PosteRate $rate): RedirectResponse
    {
        $data = $request->validate([
            'max_weight_kg' => 'required|numeric|min:0.001',
            'cost_eur'      => 'required|numeric|min:0',
        ]);

        $rate->update($data);

        session()->flash('success', 'Rate updated.');

        return redirect()->route('services.zones.index', $service);
    }

    public function destroy(PosteService $service, PosteZone $zone, PosteRate $rate): RedirectResponse
    {
        $rate->delete();

        session()->flash('success', 'Rate deleted.');

        return redirect()->route('services.zones.index', $service);
    }
}
