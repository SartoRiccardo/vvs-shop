<?php

use Illuminate\Http\UploadedFile;
use Webkul\Theme\Models\ThemeCustomization;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

it('should returns the theme index page', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.settings.themes.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.settings.themes.index.title'))
        ->assertSeeText(trans('admin::app.settings.themes.index.create-btn'));
});

it('should fail the validation with errors when certain field not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should fail the validation with errors when correct type not provided when store the theme', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type' => 'INVALID_TYPE',
    ])
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should store the newly created theme', function () {
    // Arrange.
    $lastThemeId = ThemeCustomization::factory()->create()->id + 1;

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.store'), [
        'type' => $type = fake()->randomElement([
            'product_carousel',
            'category_carousel',
            'image_carousel',
            'footer_links',
            'services_content',
        ]),
        'name' => $name = fake()->name(),
        'sort_order' => $lastThemeId,
        'channel_id' => $channelId = core()->getCurrentChannel()->id,
        'theme_code' => $themeCode = core()->getCurrentChannel()->theme,
    ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.settings.themes.edit', $lastThemeId));

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $lastThemeId,
                'type' => $type,
                'name' => $name,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
            ],
        ],
    ]);
});

it('should fail the validation with errors when correct type not provided when update the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('sort_order')
        ->assertJsonValidationErrorFor('type')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('theme_code')
        ->assertUnprocessable();
});

it('should update the theme customizations', function () {
    $theme = ThemeCustomization::factory()->create();

    $data = [];

    switch ($theme->type) {
        case ThemeCustomization::PRODUCT_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title' => fake()->title(),
                    'filters' => [
                        'sort' => 'name-desc',
                        'limit' => '12',
                        'new' => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::CATEGORY_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    'title' => fake()->title(),
                    'filters' => [
                        'sort' => 'desc',
                        'limit' => '10',
                        'parent_id' => '1',
                    ],
                ],
            ];

            break;

        case ThemeCustomization::IMAGE_CAROUSEL:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title' => fake()->title(),
                        'link' => fake()->url(),
                        'image' => UploadedFile::fake()->image(fake()->word().'.png', 640, 480, 'png'),
                    ],
                ],
            ];

            break;

        case ThemeCustomization::FOOTER_LINKS:
            $data[app()->getLocale()] = [
                'options' => [
                    'column_1' => [
                        [
                            'url' => fake()->url(),
                            'title' => fake()->title(),
                            'sort_order' => '1',
                        ],
                    ],
                ],
            ];

            break;

        case ThemeCustomization::SERVICES_CONTENT:
            $data[app()->getLocale()] = [
                'options' => [
                    [
                        'title' => fake()->title(),
                        'description' => fake()->paragraph(),
                        'service_icon' => 'icon-truck',
                    ],
                ],
            ];

            break;
    }

    $data['locale'] = app()->getLocale();
    $data['type'] = $theme->type;
    $data['name'] = $name = fake()->name();
    $data['sort_order'] = '1';
    $data['channel_id'] = core()->getCurrentChannel()->id;
    $data['theme_code'] = core()->getCurrentChannel()->theme;
    $data['status'] = 'on';

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $theme->id,
                'type' => $theme->type,
                'name' => $name,
            ],
        ],
    ]);
});

it('should preserve arbitrary HTML verbatim in static content when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $rawHtml = <<<'HTML'
<div><picture>
  <source
    media="(max-width: 767px)"
    srcset="/storage/media/touhou-plushies-mobile-cjgh33.webp"
    width="1000"
    height="1250"
  >
  <img
    src="/storage/media/touhou-plushies-lineup-pmu8in.webp"
    width="2000"
    height="1000"
    fetchpriority="high"
    decoding="async"
    alt="Mini Kaguya and Mini Nareko plush dolls, front view"
  >
</picture></div><script>alert("XSS")</script><iframe src="https://malicious.com"></iframe><form action="/submit" method="post"><input name="data"></form><div onclick="alert('XSS')">Click me</div>
HTML;

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $rawHtml,
                'css' => 'body { color: red; }',
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that the markup is stored verbatim — picture/source, their
    // responsive attributes, and script/iframe/form/handlers alike.
    expect($translation->options['html'])->toBe($rawHtml);
});

it('should preserve safe HTML content in static content when updating theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'static_content',
    ]);

    $safeHtml = '<div class="container"><h1>Title</h1><p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p><ul><li>Item 1</li><li>Item 2</li></ul></div>';

    $safeCss = 'body { color: blue; font-size: 14px; }';

    $data = [
        app()->getLocale() => [
            'options' => [
                'html' => $safeHtml,
                'css' => $safeCss,
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'static_content',
        'name' => fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();
    $translation = $theme->translate(app()->getLocale());

    // Assert that safe HTML elements are preserved.
    expect($translation->options['html'])->toContain('<div');
    expect($translation->options['html'])->toContain('<h1>');
    expect($translation->options['html'])->toContain('<p>');
    expect($translation->options['html'])->toContain('<strong>');
    expect($translation->options['html'])->toContain('<em>');
    expect($translation->options['html'])->toContain('<ul>');
    expect($translation->options['html'])->toContain('<li>');
});

it('should not sanitize HTML for non-static content theme types', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create([
        'type' => 'product_carousel',
    ]);

    $data = [
        app()->getLocale() => [
            'options' => [
                'title' => 'Test Title',
                'filters' => [
                    'sort' => 'name-desc',
                    'limit' => '12',
                    'new' => '1',
                ],
            ],
        ],
        'locale' => app()->getLocale(),
        'type' => 'product_carousel',
        'name' => $name = fake()->name(),
        'sort_order' => '1',
        'channel_id' => core()->getCurrentChannel()->id,
        'theme_code' => core()->getCurrentChannel()->theme,
        'status' => 'on',
    ];

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.settings.themes.update', $theme->id), $data)
        ->assertRedirect(route('admin.settings.themes.index'))
        ->isRedirection();

    $theme->refresh();

    // Assert theme was updated successfully.
    $this->assertModelWise([
        ThemeCustomization::class => [
            [
                'id' => $theme->id,
                'type' => 'product_carousel',
                'name' => $name,
            ],
        ],
    ]);
});

it('should delete the theme', function () {
    // Arrange.
    $theme = ThemeCustomization::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    deleteJson(route('admin.settings.themes.delete', $theme->id))
        ->assertOk()
        ->assertJsonPath('message', trans('admin::app.settings.themes.delete-success'));

    $this->assertDatabaseMissing('theme_customizations', [
        'id' => $theme->id,
    ]);

    $this->assertDatabaseMissing('theme_customization_translations', [
        'theme_customization_id' => $theme->id,
    ]);
});
