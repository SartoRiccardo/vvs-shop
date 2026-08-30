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
        <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-5">
            @foreach ($files as $file)
                <div class="box-shadow flex flex-col gap-2.5 rounded bg-white p-4 dark:bg-gray-900">
                    <a
                        href="{{ $file['url'] }}"
                        target="_blank"
                    >
                        <img
                            src="{{ $file['url'] }}"
                            class="h-40 w-full rounded object-contain"
                            loading="lazy"
                            alt="{{ $file['name'] }}"
                        />
                    </a>

                    <p class="break-all text-xs font-semibold text-gray-600 dark:text-gray-300">
                        {{ $file['name'] }}
                    </p>

                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            readonly
                            value="{{ $file['url'] }}"
                            onclick="this.select()"
                            class="w-full rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        />

                        <button
                            type="button"
                            class="secondary-button shrink-0 px-2.5 py-1.5 text-xs"
                            onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.textContent = '✓'; setTimeout(() => this.textContent = 'Copy', 1200)"
                        >
                            Copy
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                            {{ number_format($file['size'] / 1024, 1) }} KB
                        </span>

                        <form
                            action="{{ route('admin.media.delete', $file['name']) }}"
                            method="POST"
                            onsubmit="return confirm('@lang('admin::app.media.delete-confirm')')"
                        >
                            @csrf

                            @method('DELETE')

                            <button
                                type="submit"
                                class="text-xs font-semibold text-red-600 hover:underline dark:text-red-400"
                            >
                                @lang('admin::app.media.delete-btn')
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin::layouts>
