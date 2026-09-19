@props([
    'products' => [],
    'viewAllUrl' => null,
    'title' => '',
])

@if (count($products))
    {{-- Server-rendered fallback for the products carousel. Vue replaces it with
         the API-driven carousel once it mounts; until then (and for non-JS
         crawlers) this exposes the products as real links and images. --}}
    <div class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4">
        <div class="flex justify-between">
            <h2 class="text-3xl max-md:text-2xl max-sm:text-xl">
                {{ $title }}
            </h2>

            @if ($viewAllUrl)
                <a
                    href="{{ $viewAllUrl }}"
                    class="hidden max-lg:flex"
                >
                    <p class="items-center text-xl max-md:text-base max-sm:text-sm">
                        @lang('shop::app.components.products.carousel.view-all')

                        <span class="icon-arrow-right text-2xl max-md:text-lg max-sm:text-sm"></span>
                    </p>
                </a>
            @endif
        </div>

        <div class="mt-10 flex gap-8 pb-2.5 max-md:mt-5 max-md:gap-7 max-md:pb-0 max-sm:gap-4">
            @foreach ($products as $product)
                @php ($baseImage = product_image()->getProductBaseImage($product))

                <a
                    href="/{{ $product->url_key }}"
                    class="grid w-full min-w-[291px] max-w-[291px] content-start overflow-hidden rounded-md transition-all duration-300 hover:shadow-[0_5px_10px_rgba(0,0,0,0.1)] max-md:h-fit max-md:min-w-56 max-md:max-w-full max-md:rounded-lg max-sm:min-w-[192px]"
                    aria-label="{{ $product->name }}"
                >
                    <div class="relative max-h-[300px] max-w-[291px] overflow-hidden max-md:max-h-60 max-md:max-w-full max-md:rounded-lg max-sm:max-h-[200px] max-sm:max-w-full">
                        <img
                            class="relative bg-subtleBg transition-all duration-300"
                            src="{{ $baseImage['medium_image_url'] }}"
                            alt="{{ $baseImage['alt'] ?? $product->name }}"
                            width="291"
                            height="300"
                            loading="lazy"
                        />
                    </div>

                    <div class="grid max-w-[291px] content-start gap-2.5 bg-pageBg p-2.5 max-md:gap-0 max-md:px-0 max-md:py-1.5 max-sm:min-w-[170px] max-sm:max-w-[192px]">
                        <p class="break-words text-base font-medium max-md:mb-1.5 max-md:whitespace-break-spaces max-md:leading-6 max-sm:text-sm max-sm:leading-4">
                            {{ $product->name }}
                        </p>

                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-lg font-semibold max-sm:text-sm max-sm:leading-6">
                            {!! $product->getTypeInstance()->getPriceHtml() !!}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($viewAllUrl)
            <a
                href="{{ $viewAllUrl }}"
                class="secondary-button mx-auto mt-5 block w-max rounded-2xl px-11 py-3 text-center text-base max-lg:mt-0 max-lg:hidden max-lg:py-3.5 max-md:rounded-lg"
                aria-label="{{ $title }}"
            >
                @lang('shop::app.components.products.carousel.view-all')
            </a>
        @endif
    </div>
@endif
