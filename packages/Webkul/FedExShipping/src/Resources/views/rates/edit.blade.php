<x-admin::layouts>
    <x-slot:title>Edit Rate — Zone {{ $zoneCode }}</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('fedex.zones.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← Zones</a>
        <p class="text-xl font-bold text-gray-800 dark:text-white">Edit Rate — Zone {{ $zoneCode }}</p>
    </div>

    <div class="mt-6 max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form action="{{ route('fedex.zones.rates.update', [$zoneCode, $rate]) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Zone code</label>
                <input type="text" value="{{ $zoneCode }}" disabled
                    class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Max weight (kg)</label>
                <input type="number" name="weight_max"
                    value="{{ old('weight_max', is_null($rate->weight_max) ? '' : $rate->weight_max) }}"
                    step="0.5" min="0.5"
                    placeholder="Leave blank for open-ended tail row"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p class="mt-1 text-xs text-gray-400">Leave blank to keep as an open-ended tail row.</p>
                @error('weight_max') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Flat rate (€)</label>
                <input type="number" name="flat_rate" value="{{ old('flat_rate', $rate->flat_rate) }}" step="0.01" min="0" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('flat_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Per kg rate (€)</label>
                <input type="number" name="per_kg_rate" value="{{ old('per_kg_rate', $rate->per_kg_rate) }}" step="0.0001" min="0" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                <p class="mt-1 text-xs text-gray-400">For bracket rows use 0. For tail rows, this is the interpolation rate per kg above the previous bracket.</p>
                @error('per_kg_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="primary-button">Save</button>
                <a href="{{ route('fedex.zones.index') }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</x-admin::layouts>
