<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.media.title')
    </x-slot>

    <div class="mt-3.5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.media.title')
        </p>

        <form
            action="{{ route('admin.media.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="flex items-center gap-2.5 max-sm:flex-wrap"
        >
            @csrf

            <input
                type="file"
                name="files[]"
                multiple
                accept="image/*"
                required
                class="text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-700 dark:file:bg-gray-800 dark:file:text-gray-300"
            />

            <button
                type="submit"
                class="primary-button"
            >
                @lang('admin::app.media.upload-btn')
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-md border border-green-300 bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($files->isEmpty())
        <div class="mt-10 grid place-items-center">
            <p class="text-base text-gray-500 dark:text-gray-400">
                @lang('admin::app.media.empty')
            </p>
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($files as $file)
                <div
                    class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900"
                    title="{{ $file['name'] }}"
                >
                    <img
                        src="{{ $file['url'] }}"
                        class="h-full w-full object-contain"
                        loading="lazy"
                        alt="{{ $file['name'] }}"
                    />

                    <!-- Open in a new tab -->
                    <a
                        href="{{ $file['url'] }}"
                        target="_blank"
                        class="pointer-events-none absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-gray-700 opacity-0 shadow transition-all duration-150 hover:bg-white group-hover:pointer-events-auto group-hover:opacity-100"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-4 w-4"
                        >
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                            <path d="M15 3h6v6" />
                            <path d="M10 14 21 3" />
                        </svg>
                    </a>

                    <!-- Hover overlay: filename + copy link + delete -->
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-end gap-2 bg-gradient-to-t from-black/70 via-black/20 to-transparent p-3 opacity-0 transition-opacity duration-150 group-hover:pointer-events-auto group-hover:opacity-100">
                        <p class="w-full truncate text-center text-xs font-medium text-white">
                            {{ $file['name'] }}
                        </p>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                data-url="{{ $file['url'] }}"
                                onclick="copyMediaUrl(this)"
                                class="cursor-pointer rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 transition-colors hover:bg-gray-200"
                            >
                                @lang('admin::app.media.copy-btn')
                            </button>

                            <form
                                action="{{ route('admin.media.delete', $file['name']) }}"
                                method="POST"
                                onsubmit="return confirm('@lang('admin::app.media.delete-confirm')')"
                            >
                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="cursor-pointer rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-red-500"
                                >
                                    @lang('admin::app.media.delete-btn')
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        function copyMediaUrl(button) {
            navigator.clipboard.writeText(button.dataset.url);

            const original = button.textContent;

            button.textContent = '✓';

            setTimeout(() => button.textContent = original, 1200);
        }
    </script>
</x-admin::layouts>
