<x-admin::layouts>
    <x-slot:title>Add Service</x-slot>

    <div class="flex items-center gap-3">
        <a href="{{ route('services.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">← Services</a>
        <p class="text-xl font-bold text-gray-800 dark:text-white">Add Service</p>
    </div>

    <div class="mt-6 max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form action="{{ route('services.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', '1') ? 'checked' : '' }} class="rounded" />
                <label for="active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="primary-button">Create</button>
                <a href="{{ route('services.index') }}" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</x-admin::layouts>
