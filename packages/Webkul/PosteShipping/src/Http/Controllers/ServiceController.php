<?php

namespace Webkul\PosteShipping\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Webkul\PosteShipping\Models\PosteService;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = PosteService::withCount('zones')->orderBy('name')->get();

        return view('poste_shipping::services.index', compact('services'));
    }

    public function create(): View
    {
        return view('poste_shipping::services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active'      => 'boolean',
        ]);

        $data['active'] = $request->boolean('active');

        PosteService::create($data);

        session()->flash('success', 'Service created.');

        return redirect()->route('services.index');
    }

    public function edit(PosteService $service): View
    {
        $service->load('zones.countryZones', 'zones.rates');

        return view('poste_shipping::services.edit', compact('service'));
    }

    public function update(Request $request, PosteService $service): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active'      => 'boolean',
        ]);

        $data['active'] = $request->boolean('active');

        $service->update($data);

        session()->flash('success', 'Service updated.');

        return redirect()->route('services.index');
    }

    public function destroy(PosteService $service): RedirectResponse
    {
        $service->delete();

        session()->flash('success', 'Service deleted.');

        return redirect()->route('services.index');
    }
}
