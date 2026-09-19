@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    <!-- Loop over the theme customization -->
    @foreach ($customizations as $customization)
        @php ($data = $customization->options) @endphp

        <!-- Static content -->
        @switch ($customization->type)
            @case ($customization::IMAGE_CAROUSEL)
                <!-- Image Carousel -->
                <x-shop::carousel
                    :options="$data"
                    aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
                />

                @break
            @case ($customization::STATIC_CONTENT)
                <!-- push style -->
                @if (! empty($data['css']))
                    @push ('styles')
                        <style>
                            {{ $data['css'] }}
                        </style>
                    @endpush
                @endif

                <!-- render html -->
                @if (! empty($data['html']))
                    {!! $data['html'] !!}
                @endif

                @break
            @case ($customization::CATEGORY_CAROUSEL)
                <!-- Categories carousel -->
                <x-shop::categories.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.categories.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.home.index')"
                    aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
                />

                @break
            @case ($customization::PRODUCT_CAROUSEL)
                @php
                    /**
                     * Server-rendered fallback so the raw HTML carries real
                     * product links and images (mirrors the carousel's API
                     * query: sort=created_at-desc&limit=12).
                     */
                    $ssrProducts = app(\Webkul\Product\Repositories\ProductRepository::class)
                        ->getAll([
                            'status'               => 1,
                            'visible_individually' => 1,
                            'channel_id'           => $channel->id,
                            'sort'                 => 'created_at-desc',
                            'limit'                => 12,
                        ])
                        ->items();

                    // First real category under the channel root — the store's
                    // top level. (parent_id null IS the invisible "Root"
                    // category itself, slug "root", not a linkable page.)
                    $ssrCategory = app(\Webkul\Category\Repositories\CategoryRepository::class)
                        ->findWhere([
                            'status'    => 1,
                            'parent_id' => $channel->root_category_id,
                        ])
                        ->sortBy('position')
                        ->first();

                    /**
                     * Optional admin override (theme customization) for the
                     * carousel's "View All" target. Empty = Vue keeps its
                     * search link; SSR falls back to the top category.
                     */
                    $viewMoreLink = trim($data['view_more_link'] ?? '') ?: null;
                @endphp

                <!-- Product Carousel -->
                <x-shop::products.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.products.index', $data['filters'] ?? [])"
                    :navigation-link="$viewMoreLink ?: route('shop.search.index', $data['filters'] ?? [])"
                    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
                >
                    <x-shop::products.ssr-cards
                        :products="$ssrProducts"
                        :view-all-url="$viewMoreLink ?: ($ssrCategory?->url)"
                        :title="$data['title'] ?? ''"
                    />
                </x-shop::products.carousel>

                @break
        @endswitch
    @endforeach
</x-shop::layouts>
