<x-admin::layouts>
    <x-slot:title>Add Zone — {{ $service->name }}</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('services.zones.index', $service) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← {{ $service->name }}</a>
        <p class="text-xl font-bold text-gray-800 dark:text-white">Add Zone</p>
    </div>

    <div class="mt-6 max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form action="{{ route('services.zones.store', $service) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Countries</label>
                <select name="country_codes[]" multiple
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" style="height: 200px">
                    @foreach ($countries as $country)
                        <option value="{{ $country->code }}" {{ in_array($country->code, old('country_codes', [])) ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->code }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Hold Ctrl / Cmd to select multiple.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="primary-button">Create</button>
                <a href="{{ route('services.zones.index', $service) }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</x-admin::layouts>
