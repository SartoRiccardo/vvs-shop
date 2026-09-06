{{--
    Reusable newsletter subscription form — just the email input and the
    subscribe button, nothing else. Drop it anywhere in a shop view:
    x-shop::newsletter.

    Placement-specific styling: classes passed to the component are merged
    onto the form element, and the stable `newsletter-*` class hooks on the
    inner elements can be targeted from CSS or arbitrary variants.
--}}
<x-shop::form
    :action="route('shop.subscription.store')"
    {{ $attributes->merge(['class' => 'newsletter-form flex max-w-full items-start gap-2 max-sm:flex-col']) }}
>
    <div class="w-full">
        <x-shop::form.control-group.control
            type="email"
            class="newsletter-input block w-full rounded-xl border-2 border-navyBlue/15 bg-black/5 px-5 py-4 text-base max-md:p-3.5 max-sm:rounded-lg max-sm:border-2 max-sm:p-2 max-sm:text-sm"
            name="email"
            rules="required|email"
            label="Email"
            :aria-label="trans('shop::app.components.layouts.footer.email')"
            placeholder="email@example.com"
        />

        <x-shop::form.control-group.error control-name="email" />
    </div>

    <button
        type="submit"
        class="newsletter-button flex w-max shrink-0 items-center rounded-xl bg-pageBg px-7 py-2.5 font-medium hover:bg-subtleBg max-md:px-5 max-md:text-xs max-sm:w-full max-sm:justify-center max-sm:rounded-lg max-sm:px-4 max-sm:py-2"
    >
        @lang('shop::app.components.layouts.footer.subscribe')
    </button>
</x-shop::form>
