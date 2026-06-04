<x-admin::layouts>
    <x-slot:title>FedEx FICP — Zones</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">FedEx FICP — Zones &amp; Rates</p>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 space-y-6">
        @forelse ($zones as $zoneData)
            <div id="zone-{{ $zoneData->zone_code }}" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                {{-- Zone header --}}
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <p class="font-semibold text-gray-800 dark:text-white">Zone {{ $zoneData->zone_code }}</p>
                        @if ($zoneData->is_european_zone)
                            <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">European zone</span>
                        @else
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">World zone</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $zoneData->countries_count }} {{ Str::plural('country', $zoneData->countries_count) }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('fedex.zones.rates.create', $zoneData->zone_code) }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">+ Rate</a>
                        <a href="{{ route('fedex.zones.edit', $zoneData->zone_code) }}" class="text-sm font-medium text-gray-600 hover:underline dark:text-gray-400">Edit countries</a>
                    </div>
                </div>

                <div class="px-6 py-4">
                    {{-- Countries --}}
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Countries</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($zoneData->countries as $country)
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $country->country_code }}</span>
                            @empty
                                <span class="text-xs text-gray-400">None assigned</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Rates --}}
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Rates</p>
                        @if ($zoneData->rates->isEmpty())
                            <p class="text-xs text-gray-400">No rates yet.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-gray-400">
                                        <th class="pb-1 pr-8">Max weight (kg)</th>
                                        <th class="pb-1 pr-8">Flat rate (€)</th>
                                        <th class="pb-1 pr-8">Per kg rate (€)</th>
                                        <th class="pb-1"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($zoneData->rates as $rate)
                                        <tr>
                                            <td class="py-1.5 pr-8">
                                                @if (is_null($rate->weight_max))
                                                    <span class="italic text-gray-400">Open-ended</span>
                                                @else
                                                    {{ number_format($rate->weight_max, 2) }}
                                                @endif
                                            </td>
                                            <td class="py-1.5 pr-8">€{{ number_format($rate->flat_rate, 2) }}</td>
                                            <td class="py-1.5 pr-8">
                                                @if ($rate->per_kg_rate > 0)
                                                    €{{ number_format($rate->per_kg_rate, 4) }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="py-1.5 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="{{ route('fedex.zones.rates.edit', [$zoneData->zone_code, $rate]) }}" class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></a>
                                                    <form action="{{ route('fedex.zones.rates.destroy', [$zoneData->zone_code, $rate]) }}" method="POST" class="inline" onsubmit="return confirm('Delete rate?')">
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
                No zones found.
            </div>
        @endforelse
    </div>
</x-admin::layouts>
