<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.errors.404.title')
    </x-slot>

    <!-- 404 Content, centered vertically & horizontally in the remaining viewport (header is min-h-[78px]) -->
    <div class="container flex min-h-[calc(100vh_-_78px)] items-center justify-center px-[60px] max-1180:px-8 max-md:px-4">
        @if ($content = core()->getConfigData('general.content.error_404.content'))
            <div class="w-full py-16 max-md:py-10">
                {!! $content !!}
            </div>
        @else
            <div class="py-16 text-center max-md:py-10">
                <h1 class="text-3xl font-semibold max-md:text-xl">
                    @lang('shop::app.errors.404.title')
                </h1>

                <p class="mt-4 text-lg text-mutedText max-md:text-sm">
                    @lang('shop::app.errors.404.description')
                </p>

                <a
                    href="{{ route('shop.home.index') }}"
                    class="m-auto mt-8 block w-max cursor-pointer rounded-[45px] bg-navyBlue px-10 py-4 text-center text-base font-medium text-white max-sm:mb-10 max-sm:px-6 max-sm:text-sm"
                >
                    @lang('shop::app.errors.go-to-home')
                </a>
            </div>
        @endif
    </div>
</x-shop::layouts>
