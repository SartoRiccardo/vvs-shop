<!--
    Newsletter subscription page. All content is customizable from
    Admin > Configure > Customer > Settings > Newsletter.
-->
@php
    $config = fn ($key) => core()->getConfigData("customer.settings.newsletter.$key");

    $pageTitle = $config('page_title') ?: trans('shop::app.subscription.page-title');

    $metaTitle = $config('page_meta_title') ?: trans('shop::app.subscription.page-meta-title');
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta name="title" content="{{ $metaTitle }}" />

    @if ($metaDescription = $config('page_meta_description'))
        <meta name="description" content="{{ $metaDescription }}" />
    @endif

    @if ($metaKeywords = $config('page_meta_keywords'))
        <meta name="keywords" content="{{ $metaKeywords }}" />
    @endif
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $metaTitle }}
    </x-slot:title>

    <!-- Page Content -->
    <div class="container mt-8 px-[60px] max-lg:px-8">
        {!! $config('page_content_before') !!}

        <div class="mx-auto mt-4 max-w-md">
            <h1 class="text-center text-3xl italic leading-[45px] text-navyBlue max-md:text-2xl max-sm:text-lg">
                {{ $pageTitle }}
            </h1>

            <p class="mt-1 text-center text-xs">
                {{ $config('page_subtitle') ?: trans('shop::app.subscription.page-subtitle') }}
            </p>

            <x-shop::newsletter class="mx-auto mt-6 w-[420px] max-w-full max-sm:w-full" />
        </div>

        {!! $config('page_content_after') !!}
    </div>
</x-shop::layouts>
