<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\CurrencyRepository;

class Currency
{
    /**
     * Countries (ISO 3166-1 alpha-2) that default to EUR pricing.
     * Read from the CF-IPCountry header set by Cloudflare.
     *
     * EU-27 plus EEA (IS, NO, LI), Switzerland and the UK.
     */
    protected const EUROPEAN_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
        'IS', 'NO', 'LI', 'CH', 'GB',
    ];

    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected CurrencyRepository $currencyRepository) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currencies = core()->getCurrentChannel()->currencies->pluck('code')->toArray();
        $currencyCode = core()->getRequestedLocaleCode('currency', false);

        if (! $currencyCode || ! in_array($currencyCode, $currencies)) {
            $currencyCode = session()->get('currency');
        }

        if (! $currencyCode || ! in_array($currencyCode, $currencies)) {
            $currencyCode = $this->getGeoCurrency($request, $currencies);
        }

        if (! $currencyCode || ! in_array($currencyCode, $currencies)) {
            $currencyCode = core()->getCurrentChannel()->base_currency->code;
        }

        core()->setCurrentCurrency($currencyCode);
        session()->put('currency', $currencyCode);
        unset($request['currency']);

        return $next($request);
    }

    /**
     * Guess the currency from the visitor's country (Cloudflare CF-IPCountry).
     * Falls back to null so the channel base currency is used.
     *
     * @param  Request  $request
     * @param  array  $currencies
     * @return string|null
     */
    protected function getGeoCurrency($request, $currencies)
    {
        $country = $request->header('cf-ipcountry');

        if (! $country) {
            return null;
        }

        $code = in_array($country, self::EUROPEAN_COUNTRIES) ? 'EUR' : 'USD';

        return in_array($code, $currencies) ? $code : null;
    }
}
