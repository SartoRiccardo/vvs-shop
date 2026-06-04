<x-admin::layouts>
    <x-slot:title>Edit Zone {{ $zoneCode }}</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('fedex.zones.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← Zones</a>
        <p class="text-xl font-bold text-gray-800 dark:text-white">Edit Zone {{ $zoneCode }}</p>
    </div>

    <div class="mt-6 max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form action="{{ route('fedex.zones.update', $zoneCode) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Zone code</label>
                <input type="text" value="{{ $zoneCode }}" disabled
                    class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_european_zone" value="0" />
                <input type="checkbox" name="is_european_zone" id="is_european_zone" value="1"
                    {{ old('is_european_zone', $zone->is_european_zone) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 dark:border-gray-600" />
                <label for="is_european_zone" class="text-sm font-medium text-gray-700 dark:text-gray-300">European zone</label>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Countries</label>
                <select name="country_codes[]" multiple
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" style="height: 220px">
                    @foreach ($countries as $country)
                        <option value="{{ $country->code }}" {{ in_array($country->code, old('country_codes', $assignedCodes)) ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->code }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Hold Ctrl / Cmd to select multiple. Countries moved here are removed from their previous zone.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="primary-button">Save</button>
                <a href="{{ route('fedex.zones.index') }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</x-admin::layouts>
