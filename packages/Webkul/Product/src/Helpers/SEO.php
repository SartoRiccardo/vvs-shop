<?php

namespace Webkul\Product\Helpers;

use Illuminate\Support\Facades\Storage;
use Webkul\Category\Contracts\Category;
use Webkul\Product\Contracts\Product;

class SEO
{
    /**
     * Returns product json ld data for product
     *
     * @param  Product  $product
     * @return string
     */
    public function getProductJsonLd($product)
    {
        $data = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description,
            'url' => route('shop.product_or_category.index', $product->url_key),
        ];

        if (core()->getConfigData('catalog.rich_snippets.products.show_sku')) {
            $data['sku'] = $product->sku;
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_weight')) {
            $data['weight'] = $product->weight;
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_categories')) {
            $data['categories'] = $this->getProductCategories($product);
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_images')) {
            $data['image'] = $this->getProductImages($product);
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_reviews')) {
            $data['review'] = $this->getProductReviews($product);
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_ratings')) {
            $data['aggregateRating'] = $this->getProductAggregateRating($product);
        }

        if (core()->getConfigData('catalog.rich_snippets.products.show_offers')) {
            $data['offers'] = $this->getProductOffers($product);
        }

        return json_encode($data);
    }

    /**
     * Returns product categories
     *
     * @param  Product  $product
     * @return string
     */
    public function getProductCategories($product)
    {
        $categories = $product->categories;

        $names = [];

        foreach ($categories as $key => $category) {
            $names[] = $category->name;
        }

        return implode(', ', $names);
    }

    /**
     * Returns product images
     *
     * @param  Product  $product
     * @return array
     */
    public function getProductImages($product)
    {
        $images = [];

        foreach ($product->images as $image) {
            if (! Storage::has($image->path)) {
                continue;
            }

            $images[] = $image->url;
        }

        return $images;
    }

    /**
     * Returns product reviews
     *
     * @param  Product  $product
     * @return array
     */
    public function getProductReviews($product)
    {
        $reviews = [];

        foreach ($product->reviews()->where('status', 'approved')->get() as $review) {
            $reviews[] = [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $review->rating,
                    'bestRating' => '5',
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $review->name,
                ],
            ];
        }

        return $reviews;
    }

    /**
     * Returns product average ratings
     *
     * @param  Product  $product
     * @return array
     */
    public function getProductAggregateRating($product)
    {
        $reviewHelper = app('Webkul\Product\Helpers\Review');

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => $reviewHelper->getAverageRating($product),
            'reviewCount' => $reviewHelper->getTotalReviews($product),
        ];
    }

    /**
     * Returns product average ratings
     *
     * @param  Product  $product
     * @return array
     */
    public function getProductOffers($product)
    {
        return [
            '@type' => 'Offer',
            'priceCurrency' => core()->getCurrentCurrencyCode(),
            'price' => $product->getTypeInstance()->getMinimalPrice(),
            'availability' => 'https://schema.org/InStock',
        ];
    }

    /**
     * Returns breadcrumb list json ld data for a category, walking from the
     * store root down to the category itself.
     *
     * @param  Category  $category
     * @return string
     */
    public function getCategoryBreadcrumbJsonLd($category)
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
        ];

        $position = 2;

        foreach ($category->ancestors as $ancestor) {
            if (! $ancestor->parent_id) {
                continue;
            }

            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->name,
                'item' => $ancestor->url,
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $category->name,
            'item' => $category->url,
        ];

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ]);
    }

    /**
     * Returns organization json ld data for the current channel.
     *
     * @return string
     */
    public function getOrganizationJsonLd()
    {
        $channel = core()->getCurrentChannel();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $channel->name,
            'url' => url('/'),
        ];

        if (! empty($channel->logo_url)) {
            $data['logo'] = $channel->logo_url;
        }

        return json_encode($data);
    }

    /**
     * Returns website json ld data for the current channel, with an optional
     * search action so search engines can link straight to storefront search.
     *
     * @return string
     */
    public function getWebsiteJsonLd()
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => core()->getCurrentChannel()->name,
            'url' => url('/'),
        ];

        if (core()->getConfigData('catalog.rich_snippets.general.show_search_action')) {
            $data['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/search?query={search_term_string}'),
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return json_encode($data);
    }
}
