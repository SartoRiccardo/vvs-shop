<?php

namespace Webkul\FedExShipping\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Webkul\FedExShipping\Models\FedExRate;

class RateController extends Controller
{
    public function create(string $zoneCode): View
    {
        return view('fedex_shipping::rates.create', compact('zoneCode'));
    }

    public function store(Request $request, string $zoneCode): RedirectResponse
    {
        $data = $request->validate([
            'weight_max'  => 'nullable|numeric|min:0',
            'flat_rate'   => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
        ]);

        FedExRate::create([
            'zone_code'   => $zoneCode,
            'weight_max'  => isset($data['weight_max']) && $data['weight_max'] !== '' ? $data['weight_max'] : null,
            'flat_rate'   => $data['flat_rate'],
            'per_kg_rate' => $data['per_kg_rate'],
        ]);

        session()->flash('success', 'Rate added.');

        return redirect()->to(route('fedex.zones.index').'#zone-'.$zoneCode);
    }

    public function edit(string $zoneCode, FedExRate $rate): View
    {
        return view('fedex_shipping::rates.edit', compact('zoneCode', 'rate'));
    }

    public function update(Request $request, string $zoneCode, FedExRate $rate): RedirectResponse
    {
        $data = $request->validate([
            'weight_max'  => 'nullable|numeric|min:0',
            'flat_rate'   => 'required|numeric|min:0',
            'per_kg_rate' => 'required|numeric|min:0',
        ]);

        $rate->update([
            'weight_max'  => isset($data['weight_max']) && $data['weight_max'] !== '' ? $data['weight_max'] : null,
            'flat_rate'   => $data['flat_rate'],
            'per_kg_rate' => $data['per_kg_rate'],
        ]);

        session()->flash('success', 'Rate updated.');

        return redirect()->route('fedex.zones.index');
    }

    public function destroy(string $zoneCode, FedExRate $rate): RedirectResponse
    {
        $rate->delete();

        session()->flash('success', 'Rate deleted.');

        return redirect()->route('fedex.zones.index');
    }
}
