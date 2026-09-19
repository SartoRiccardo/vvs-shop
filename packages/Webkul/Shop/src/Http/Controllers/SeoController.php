<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Response;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Helpers\Seo as SeoHelper;

class SeoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected SeoHelper $seoHelper) {}

    /**
     * Serve the storefront robots.txt from the admin-configured content.
     *
     * @return Response
     */
    public function robots()
    {
        return response($this->seoHelper->robotsContent())
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve the storefront sitemap.xml, generated live from the catalog and
     * CMS content so every URL always derives from the current APP_URL
     * (no cached absolute URLs to break on a domain switch).
     *
     * @return Response
     */
    public function sitemap()
    {
        $channel = core()->getCurrentChannel();

        $urls = collect()
            ->push(url('/'))
            ->merge(
                app(CategoryRepository::class)
                    ->findWhere(['status' => 1])
                    // Skip the invisible tree root ("Root") — real categories only.
                    ->reject(fn ($category) => $category->id == $channel->root_category_id)
                    ->pluck('url')
            )
            ->merge(
                collect(app(ProductRepository::class)
                    ->getAll([
                        'status' => 1,
                        'visible_individually' => 1,
                        'channel_id' => $channel->id,
                        'limit' => 1000,
                    ])
                    ->items())
                    // route() throws on a null parameter, so guard each entry.
                    ->map(fn ($product) => $product->url_key
                        ? route('shop.product_or_category.index', $product->url_key)
                        : null)
            )
            ->merge(
                // Channel scoping mirrors how PageController resolves pages.
                app(PageRepository::class)
                    ->whereHas('channels', fn ($query) => $query->where('id', $channel->id))
                    ->all()
                    ->map(fn ($page) => $page->url_key
                        ? route('shop.cms.page', $page->url_key)
                        : null)
            )
            ->push(route('shop.subscription.index'))
            ->filter()
            ->unique()
            ->values();

        return response()
            ->view('shop::seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
