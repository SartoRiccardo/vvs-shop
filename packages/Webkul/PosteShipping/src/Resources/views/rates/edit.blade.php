<x-admin::layouts>
    <x-slot:title>Edit Rate</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('services.zones.index', $service) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← {{ $service->name }}</a>
        <p class="text-xl font-bold text-gray-800 dark:text-white">Edit Rate — {{ $zone->name }}</p>
    </div>

    <div class="mt-6 max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form action="{{ route('services.zones.rates.update', [$service, $zone, $rate]) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Max weight (kg)</label>
                <input type="number" name="max_weight_kg" value="{{ old('max_weight_kg', $rate->max_weight_kg) }}" step="0.001" min="0.001" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('max_weight_kg') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cost (EUR)</label>
                <input type="number" name="cost_eur" value="{{ old('cost_eur', $rate->cost_eur) }}" step="0.01" min="0" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('cost_eur') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="primary-button">Save</button>
                <a href="{{ route('services.zones.index', $service) }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</x-admin::layouts>
