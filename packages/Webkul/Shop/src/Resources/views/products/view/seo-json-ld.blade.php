<!-- Structured data: Product + Offer, and Home > Category > Product breadcrumbs. -->
@php
    $typeInstance = $product->getTypeInstance();

    $galleryImages = collect(product_image()->getGalleryImages($product))
        ->pluck('large_image_url')
        ->unique()
        ->values()
        ->all();

    $productJsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $galleryImages,
        'description' => trim(strip_tags($product->description)),
        'sku' => $product->sku,
        'brand' => [
            '@type' => 'Brand',
            'name' => core()->getConfigData('general.seo.open_graph.site_name')
                ?: core()->getCurrentChannel()->name,
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('shop.product_or_category.index', $product->url_key),
            'priceCurrency' => core()->getCurrentCurrencyCode(),
            'price' => $typeInstance->getMinimalPrice(),
            /**
             * Computed at render so it never goes stale; signals the price is
             * current. 90 days is the customary window.
             */
            'priceValidUntil' => now()->addDays(90)->format('Y-m-d'),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => $typeInstance->isSaleable()
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
        ],
    ]);
@endphp

<script type="application/ld+json">
    {!! json_encode($productJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@php
    $breadcrumbItems = [[
        '@type' => 'ListItem',
        'position' => 1,
        'name' => trans('shop::app.customers.account.home'),
        'item' => url('/'),
    ]];

    $category = $product->categories->first();

    if ($category) {
        $position = 2;

        foreach ($category->ancestors as $ancestor) {
            if (! $ancestor->parent_id) {
                continue;
            }

            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->name,
                'item' => $ancestor->url,
            ];
        }

        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $category->name,
            'item' => $category->url,
        ];
    }

    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => count($breadcrumbItems) + 1,
        'name' => $product->name,
        'item' => route('shop.product_or_category.index', $product->url_key),
    ];

    $breadcrumbJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];
@endphp

<script type="application/ld+json">
    {!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
