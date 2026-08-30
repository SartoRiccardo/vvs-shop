<?php

namespace Webkul\Shop\Helpers;

use Illuminate\Support\Str;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;

class Seo
{
    /**
     * Route name prefixes of utility pages (checkout, account, search…) that
     * search engines have no business indexing.
     */
    protected array $noindexRoutePrefixes = [
        'shop.checkout.',
        'shop.customer.',
        'shop.customers.',
        'shop.search.',
        'shop.compare.',
    ];

    /**
     * Cached entity resolved from the fallback URL for the current request.
     *
     * @var object|null
     */
    protected $resolvedEntity;

    /**
     * Has the fallback entity lookup already been attempted?
     */
    protected bool $entityResolved = false;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Canonical URL for the current page. Falls back to the current URL
     * without its query string for anything not handled explicitly.
     *
     * @return string
     */
    public function canonicalUrl()
    {
        $routeName = request()->route()?->getName();

        if ($routeName === 'shop.home.index') {
            return url('/');
        }

        if ($routeName === 'shop.cms.page') {
            return route('shop.cms.page', request()->route('slug'));
        }

        if ($entity = $this->getFallbackEntity()) {
            if ($entity instanceof Product) {
                return route('shop.product_or_category.index', $entity->url_key);
            }

            /**
             * Category pages: self-canonical per page, keeping the page
             * number so paginated URLs stay crawlable but distinct.
             */
            $url = $entity->url;

            if ((int) request()->query('page') > 1) {
                $url .= '?page='.(int) request()->query('page');
            }

            return $url;
        }

        return url()->current();
    }

    /**
     * Whether the current page should carry a noindex robots meta. Tied to
     * the general.seo.noindex.utility_pages config toggle.
     *
     * @return bool
     */
    public function shouldNoindex()
    {
        if (! core()->getConfigData('general.seo.noindex.utility_pages')) {
            return false;
        }

        $routeName = request()->route()?->getName() ?? '';

        foreach ($this->noindexRoutePrefixes as $prefix) {
            if (Str::startsWith($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Open Graph / Twitter meta tags for the current page. Product pages
     * emit their own tags in the product view, so this covers everything
     * else that has real content: home, CMS pages and categories.
     *
     * Returns an array of ['attribute' => 'property|name', 'key' => …, 'content' => …].
     *
     * @return array
     */
    public function openGraphMeta()
    {
        if (! core()->getConfigData('general.seo.open_graph.enable')) {
            return [];
        }

        $siteName = core()->getConfigData('general.seo.open_graph.site_name')
            ?: core()->getCurrentChannel()->name;

        $routeName = request()->route()?->getName();

        $details = match ($routeName) {
            'shop.home.index' => $this->homeOpenGraphDetails($siteName),
            'shop.cms.page' => $this->cmsOpenGraphDetails($siteName),
            'shop.product_or_category.index' => $this->categoryOpenGraphDetails($siteName),
            default => null,
        };

        if (! $details) {
            return [];
        }

        $tags = [
            ['attribute' => 'property', 'key' => 'og:site_name',   'content' => $siteName],
            ['attribute' => 'property', 'key' => 'og:type',        'content' => $details['type']],
            ['attribute' => 'property', 'key' => 'og:title',       'content' => $details['title']],
            ['attribute' => 'property', 'key' => 'og:description', 'content' => $details['description']],
            ['attribute' => 'property', 'key' => 'og:url',         'content' => $details['url']],
            ['attribute' => 'name',     'key' => 'twitter:card',   'content' => $details['image'] ? 'summary_large_image' : 'summary'],
            ['attribute' => 'name',     'key' => 'twitter:title',  'content' => $details['title']],
            ['attribute' => 'name',     'key' => 'twitter:description', 'content' => $details['description']],
        ];

        if ($details['image']) {
            $tags[] = ['attribute' => 'property', 'key' => 'og:image',      'content' => $details['image']];
            $tags[] = ['attribute' => 'name',     'key' => 'twitter:image', 'content' => $details['image']];
        }

        if ($twitterSite = core()->getConfigData('general.seo.open_graph.twitter_site')) {
            $handle = str_starts_with($twitterSite, '@') ? $twitterSite : '@'.$twitterSite;

            $tags[] = ['attribute' => 'name', 'key' => 'twitter:site', 'content' => $handle];
        }

        return $tags;
    }

    /**
     * Plain-text robots.txt content, either the admin-configured body or a
     * permissive default when the feature is disabled.
     *
     * @return string
     */
    public function robotsContent()
    {
        if (! core()->getConfigData('general.seo.robots.enable')) {
            return "User-agent: *\nDisallow:\n";
        }

        return core()->getConfigData('general.seo.robots.content')
            ?: "User-agent: *\nDisallow:\n";
    }

    /**
     * Home page share details.
     *
     * @param  string  $siteName
     * @return array
     */
    protected function homeOpenGraphDetails($siteName)
    {
        $channel = core()->getCurrentChannel();

        $homeSeo = $channel->home_seo ?? [];

        return [
            'type' => 'website',
            'title' => $homeSeo['meta_title'] ?? $siteName,
            'description' => Str::limit(strip_tags($homeSeo['meta_description'] ?? '')) ?: $siteName,
            'url' => url('/'),
            'image' => $channel->logo_url,
        ];
    }

    /**
     * CMS page share details.
     *
     * @param  string  $siteName
     * @return array|null
     */
    protected function cmsOpenGraphDetails($siteName)
    {
        $page = app('Webkul\CMS\Repositories\PageRepository')
            ->whereTranslation('url_key', request()->route('slug'))
            ->first();

        if (! $page) {
            return null;
        }

        return [
            'type' => 'article',
            'title' => $page->meta_title ?: $page->page_title,
            'description' => Str::limit(strip_tags($page->meta_description ?: $page->html_content)),
            'url' => $this->canonicalUrl(),
            'image' => core()->getCurrentChannel()->logo_url,
        ];
    }

    /**
     * Category share details. Returns null on product URLs — those pages
     * emit their own Open Graph tags.
     *
     * @param  string  $siteName
     * @return array|null
     */
    protected function categoryOpenGraphDetails($siteName)
    {
        $category = $this->getFallbackEntity();

        if (! $category instanceof Category) {
            return null;
        }

        return [
            'type' => 'website',
            'title' => $category->meta_title ?: $category->name,
            'description' => Str::limit(strip_tags($category->meta_description ?: $category->description)) ?: $siteName,
            'url' => $this->canonicalUrl(),
            'image' => $category->banner_url ?: core()->getCurrentChannel()->logo_url,
        ];
    }

    /**
     * Resolve (and cache) the product or category a fallback URL points to,
     * mirroring the lookup order of ProductsCategoriesProxyController.
     *
     * @return object|null
     */
    protected function getFallbackEntity()
    {
        if ($this->entityResolved) {
            return $this->resolvedEntity;
        }

        $this->entityResolved = true;

        if (request()->route()?->getName() !== 'shop.product_or_category.index') {
            return $this->resolvedEntity = null;
        }

        $slugOrURLKey = urldecode(trim(request()->getPathInfo(), '/'));

        /**
         * Same shape check as the proxy controller — anything else (query
         * strings, file extensions…) is served by other routes.
         */
        if (! preg_match('/^([\p{L}\p{N}\p{M}\x{0900}-\x{097F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}_-]+\/?)+$/u', $slugOrURLKey)) {
            return $this->resolvedEntity = null;
        }

        $category = $this->categoryRepository->findBySlug($slugOrURLKey);

        if ($category) {
            return $this->resolvedEntity = $category;
        }

        $product = $this->productRepository->findBySlug($slugOrURLKey);

        if (
            $product
            && $product->url_key
            && $product->visible_individually
            && $product->status
        ) {
            return $this->resolvedEntity = $product;
        }

        return $this->resolvedEntity = null;
    }
}
