<!-- Structured data: Product + Offer, and Home > Category > Product breadcrumbs. -->
@php
    $typeInstance = $product->getTypeInstance();

    $galleryImages = collect(product_image()->getGalleryImages($product))
        ->pluck('large_image_url')
        ->unique()
        ->values()
        ->all();

    /**
     * Configured under Settings → Configuration → General → SEO → Return
     * policy (schema.org). Needs a return window AND an applicable country
     * (Google requires the latter); otherwise nothing is emitted.
     */
    $returnDays = core()->getConfigData('general.seo.return_policy.merchant_return_days');

    $applicableCountry = core()->getConfigData('general.seo.return_policy.applicable_country')
        ?: core()->getConfigData('sales.shipping.origin.country');

    $returnPolicy = $returnDays !== null && $returnDays !== '' && $applicableCountry ? array_filter([
        '@type' => 'MerchantReturnPolicy',
        'applicableCountry' => $applicableCountry,
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays' => (int) $returnDays,
        'returnMethod' => 'https://schema.org/'.(core()->getConfigData('general.seo.return_policy.return_method') ?: 'ReturnByMail'),
        'returnFees' => 'https://schema.org/'.(core()->getConfigData('general.seo.return_policy.return_fees') ?: 'FreeReturn'),
        'merchantReturnLink' => core()->getConfigData('general.seo.return_policy.return_policy_url'),
    ]) : null;

    /**
     * Geo-matched shipping details, configured under Sales → Shipping
     * settings → Shipping details (schema.org). Destination is the geo
     * header when present, else the fallback country; the rate is the
     * cheapest active one for this product's weight. No rate for a country
     * = nothing emitted.
     */
    $destinationCountry = request()->header('cf-ipcountry')
        ?: core()->getConfigData('sales.shipping.schema.fallback_country')
        ?: 'US';

    $schemaShipping = class_exists(\Webkul\PosteShipping\Helpers\SchemaShipping::class)
        ? app(\Webkul\PosteShipping\Helpers\SchemaShipping::class)->getCheapestRate(
            $destinationCountry,
            (float) $product->weight + 0.1
        )
        : null;

    $handlingMin = core()->getConfigData('sales.shipping.schema.handling_days_min');
    $handlingMax = core()->getConfigData('sales.shipping.schema.handling_days_max');
    $transitMin = core()->getConfigData('sales.shipping.schema.transit_days_min');
    $transitMax = core()->getConfigData('sales.shipping.schema.transit_days_max');

    $deliveryTime = $handlingMin !== null && $handlingMin !== ''
        && $handlingMax !== null && $handlingMax !== ''
        && $transitMin !== null && $transitMin !== ''
        && $transitMax !== null && $transitMax !== ''
        ? [
            '@type' => 'DeliveryTimeSpecification',
            'handlingTime' => [
                '@type' => 'QuantitativeValue',
                'minValue' => (int) $handlingMin,
                'maxValue' => (int) $handlingMax,
                'unitCode' => 'DAY',
            ],
            'transitTime' => [
                '@type' => 'QuantitativeValue',
                'minValue' => (int) $transitMin,
                'maxValue' => (int) $transitMax,
                'unitCode' => 'DAY',
            ],
        ]
        : null;

    $shippingDetails = $schemaShipping ? array_filter([
        '@type' => 'OfferShippingDetails',
        'shippingRate' => [
            '@type' => 'MonetaryAmount',
            'value' => round($schemaShipping['price'], 2),
            'currency' => core()->getCurrentCurrencyCode(),
        ],
        'shippingDestination' => [
            '@type' => 'DefinedRegion',
            'addressCountry' => $destinationCountry,
        ],
        'deliveryTime' => $deliveryTime,
    ]) : null;

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
        'offers' => array_filter([
            '@type' => 'Offer',
            'url' => route('shop.product_or_category.index', $product->url_key),
            'priceCurrency' => core()->getCurrentCurrencyCode(),
            'price' => $typeInstance->getDisplayedMinimalPrice(),
            /**
             * Computed at render so it never goes stale; signals the price is
             * current. 90 days is the customary window.
             */
            'priceValidUntil' => now()->addDays(90)->format('Y-m-d'),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => $typeInstance->isSaleable()
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'hasMerchantReturnPolicy' => $returnPolicy,
            'shippingDetails' => $shippingDetails,
        ]),
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
