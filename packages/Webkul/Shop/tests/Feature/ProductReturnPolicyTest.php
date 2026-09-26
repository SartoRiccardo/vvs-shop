<?php

use Webkul\Core\Models\CoreConfig;
use Webkul\Faker\Helpers\Product as ProductFaker;

use function Pest\Laravel\get;

function seedReturnPolicyConfig(array $values): void
{
    foreach ($values as $code => $value) {
        CoreConfig::query()->create([
            'code' => $code,
            'value' => $value,
        ]);
    }
}

function productJsonLd(string $html): array
{
    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

    return json_decode($matches[1], true);
}

it('emits the configured return policy in product structured data and omits it when unset', function () {
    // Arrange
    /**
     * The suite runs against a live database, so clear any committed config
     * rows for these codes first — a stale row would win the lookup.
     */
    CoreConfig::query()->where('code', 'like', 'general.seo.return_policy.%')->delete();

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    seedReturnPolicyConfig([
        'general.seo.return_policy.merchant_return_days' => '30',
        'general.seo.return_policy.applicable_country' => 'IT',
        'general.seo.return_policy.return_policy_url' => 'https://example.com/returns',
    ]);

    // Act and Assert: the offer carries the policy with defaults filled in.
    $offer = productJsonLd(get(route('shop.product_or_category.index', $product->url_key))->getContent())['offers'];

    expect($offer['hasMerchantReturnPolicy'])->toBe([
        '@type' => 'MerchantReturnPolicy',
        'applicableCountry' => 'IT',
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays' => 30,
        'returnMethod' => 'https://schema.org/ReturnByMail',
        'returnFees' => 'https://schema.org/FreeReturn',
        'merchantReturnLink' => 'https://example.com/returns',
    ]);

    // Act and Assert: no window configured, no policy emitted. Fresh product
    // so the full-page cache can't serve the first render.
    CoreConfig::query()->where('code', 'like', 'general.seo.return_policy.%')->delete();

    $offer = productJsonLd(get(route('shop.product_or_category.index', (new ProductFaker)->getSimpleProductFactory()->create()->url_key))->getContent())['offers'];

    expect($offer)->not->toHaveKey('hasMerchantReturnPolicy');
});
