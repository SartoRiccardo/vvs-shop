<x-admin::layouts>
    <x-slot:title>Poste Italiane — Services</x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">Poste Italiane — Services</p>
        <a href="{{ route('services.create') }}" class="primary-button">Add service</a>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Description</th>
                    <th class="px-6 py-3">Zones</th>
                    <th class="px-6 py-3">Active</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-4 font-medium">{{ $service->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $service->description ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('services.zones.index', $service) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                {{ $service->zones_count }} zone(s)
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $service->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $service->active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('services.edit', $service) }}" class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></a>
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Delete this service and all its zones/rates?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No services yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin::layouts>
