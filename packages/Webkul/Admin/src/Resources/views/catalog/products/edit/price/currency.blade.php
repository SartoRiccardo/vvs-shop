{!! view_render_event('bagisto.admin.catalog.product.edit.form.price.currency.before', ['product' => $product]) !!}

@php
    $currencies = $currentChannel->currencies->reject(fn ($currency) => $currency->id == $currentChannel->base_currency_id);

    $minimalPrice = $product->getTypeInstance()->getMinimalPrice();
@endphp

@if ($currencies->isNotEmpty())
    <div class="mb-2.5 mt-6">
        <p class="mb-2.5 text-base font-semibold text-gray-800 dark:text-white" v-pre>
            @lang('admin::app.catalog.products.edit.price.currency.title')
        </p>

        @foreach ($currencies as $currency)
            @php
                $controlName = 'currency_prices.' . $currency->id;

                $value = old($controlName, $product->currency_prices->firstWhere('currency_id', $currency->id)?->amount ?? '');
            @endphp

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    {{ $currency->name }} ({{ $currency->code }})

                    <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                        @lang('admin::app.catalog.products.edit.price.currency.converts-to', [
                            'price' => core()->formatPrice(core()->convertPrice($minimalPrice, $currency->code), $currency->code),
                        ])
                    </span>
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    :name="'currency_prices[' . $currency->id . ']'"
                    :rules="'nullable|numeric|min:0'"
                    :value="$value"
                    :label="$currency->name"
                />

                <x-admin::form.control-group.error :control-name="'currency_prices[' . $currency->id . ']'" />
            </x-admin::form.control-group>
        @endforeach
    </div>
@endif

{!! view_render_event('bagisto.admin.catalog.product.edit.form.price.currency.after', ['product' => $product]) !!}
