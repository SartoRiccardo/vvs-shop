@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >
        <meta 
            name="generator" 
            content="Bagisto"
        >

        @stack('meta')

        @php
            $seoHelper = app('Webkul\Shop\Helpers\Seo');
        @endphp

        {{-- Utility pages (checkout, account, search…) stay out of the index. --}}
        @if ($seoHelper->shouldNoindex())
            <meta
                name="robots"
                content="noindex, follow"
            >
        @endif

        {{-- Canonical URL for every content page. --}}
        @if (core()->getConfigData('general.seo.canonical.enable'))
            <link
                rel="canonical"
                href="{{ $seoHelper->canonicalUrl() }}"
            />
        @endif

        {{-- Open Graph / Twitter share tags (product pages emit their own). --}}
        @foreach ($seoHelper->openGraphMeta() as $ogTag)
            <meta
                {{ $ogTag['attribute'] }}="{{ $ogTag['key'] }}"
                content="{{ $ogTag['content'] }}"
            >
        @endforeach

        {{-- Sitewide structured data: Organization + WebSite (with search action). --}}
        @if (core()->getConfigData('catalog.rich_snippets.general.enable'))
            <script type="application/ld+json">
                {!! app('Webkul\Product\Helpers\SEO')->getOrganizationJsonLd() !!}
            </script>

            <script type="application/ld+json">
                {!! app('Webkul\Product\Helpers\SEO')->getWebsiteJsonLd() !!}
            </script>
        @endif

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            rel="preload" as="style"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        @stack('styles')

        @php
            $hexToRgbTriplet = fn ($hex) => implode(' ', array_map('hexdec', str_split(ltrim($hex ?: '000000', '#'), 2)));

            // Perceived luminance (ITU-R BT.601) decides which way Border and Subtle
            // Background lean when derived from Neutral — see app.css. On a light page
            // they lighten toward white; on a dark page they'd only wash out doing that,
            // so they darken toward black instead, staying a subtle surface variation.
            $pageBackgroundHex = core()->getConfigData('general.design.theme_colors.page_background') ?: '#ffffff';
            [$pageBgR, $pageBgG, $pageBgB] = array_map('hexdec', str_split(ltrim($pageBackgroundHex, '#'), 2));
            $pageBgLuminance = (0.299 * $pageBgR + 0.587 * $pageBgG + 0.114 * $pageBgB) / 255;
            $neutralMixTarget = $pageBgLuminance < 0.5 ? 'black' : 'white';
        @endphp

        <style>
            :root {
                --color-primary: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.primary')) }};
                --color-bg-brand: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.background')) }};
                --color-page-bg: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.page_background')) }};
                --color-success: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.success')) }};
                --color-link: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.link')) }};
                --color-danger: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.danger')) }};
                --color-neutral: {{ $hexToRgbTriplet(core()->getConfigData('general.design.theme_colors.neutral')) }};
                --color-neutral-mix-target: {{ $neutralMixTarget }};

                /* Border and Subtle Background derive from Neutral — see app.css. */
            }
        </style>

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        @if(core()->getConfigData('general.content.speculation_rules.enabled'))
            <script type="speculationrules">
                @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            </script>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

        @if(env('PLAUSIBLE_DOMAIN') && env('PLAUSIBLE_URL'))
            <script defer data-domain="{{ env('PLAUSIBLE_DOMAIN') }}" src="{{ env('PLAUSIBLE_URL') }}/js/script.js"></script>
        @endif

    </head>

    <body class="bg-pageBg">
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a
            href="#main"
            class="skip-to-main-content-link"
        >
            Skip to main content
        </a>

        <!-- Built With Bagisto -->
        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            @if(
                core()->getConfigData('general.gdpr.settings.enabled')
                && core()->getConfigData('general.gdpr.cookie.enabled')
            )
                <x-shop::layouts.cookie />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-pageBg">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            /**
             * Mount the application as soon as the DOM is ready instead of waiting
             * for the `load` event. All `Vue` components are registered through
             * deferred `type="module"` scripts, which always finish executing
             * before `DOMContentLoaded` fires, so every component is available
             * by the time `app.mount()` runs. Mounting on `DOMContentLoaded`
             * avoids blocking the storefront behind every image/font download.
             */
            function mountApp() {
                app.mount("#app");
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", mountApp);
            } else {
                mountApp();
            }
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
