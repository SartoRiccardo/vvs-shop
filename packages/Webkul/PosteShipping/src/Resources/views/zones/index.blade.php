<x-admin::layouts>
    <x-slot:title>{{ $service->name }} — Zones</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('services.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← Services</a>
            <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $service->name }} — Zones</p>
        </div>
        <a href="{{ route('services.zones.create', $service) }}" class="primary-button">Add zone</a>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 space-y-6">
        @forelse ($service->zones as $zone)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white">{{ $zone->name }}</p>
                        @if ($zone->description)
                            <p class="text-xs text-gray-400">{{ $zone->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('services.zones.rates.create', [$service, $zone]) }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">+ Rate</a>
                        <a href="{{ route('services.zones.edit', [$service, $zone]) }}" class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></a>
                        <form action="{{ route('services.zones.destroy', [$service, $zone]) }}" method="POST" class="inline" onsubmit="return confirm('Delete zone and all its rates?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></button>
                        </form>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Countries</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($zone->countryZones as $cz)
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $cz->country_code }}</span>
                            @empty
                                <span class="text-xs text-gray-400">None assigned</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Rates</p>
                        @if ($zone->rates->isEmpty())
                            <p class="text-xs text-gray-400">No rates yet.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-gray-400">
                                        <th class="pb-1 pr-8">Max weight (kg)</th>
                                        <th class="pb-1 pr-8">Cost (EUR)</th>
                                        <th class="pb-1"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($zone->rates->sortBy('max_weight_kg') as $rate)
                                        <tr>
                                            <td class="py-1.5 pr-8">{{ $rate->max_weight_kg }}</td>
                                            <td class="py-1.5 pr-8">€{{ number_format($rate->cost_eur, 2) }}</td>
                                            <td class="py-1.5 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('services.zones.rates.edit', [$service, $zone, $rate]) }}" class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></a>
                                                    <form action="{{ route('services.zones.rates.destroy', [$service, $zone, $rate]) }}" method="POST" class="inline" onsubmit="return confirm('Delete rate?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white px-6 py-8 text-center text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                No zones yet.
            </div>
        @endforelse
    </div>
</x-admin::layouts>
