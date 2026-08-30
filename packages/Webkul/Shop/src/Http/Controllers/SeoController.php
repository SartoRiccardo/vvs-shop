<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Response;
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
}
