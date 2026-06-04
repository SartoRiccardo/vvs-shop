<?php

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\SubscribersList;
use Webkul\Marketing\Models\Campaign;
use Webkul\Marketing\Models\Event;
use Webkul\Marketing\Models\Template;
use Webkul\SubscriberTags\Models\SubscriberTag;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('should return the campaign index page', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.marketing.communications.campaigns.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.index.title'))
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.index.create-btn'));
});

it('should returns the create page of campaigns', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.marketing.communications.campaigns.create'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.create.title'))
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.create.back-btn'));
});

it('should fail the validation with errors when certain inputs are not provided when store in campaigns', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.marketing.communications.campaigns.store'))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('subject')
        ->assertJsonValidationErrorFor('marketing_template_id')
        ->assertJsonValidationErrorFor('marketing_event_id')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('customer_group_id')
        ->assertUnprocessable();
});

it('should fail the validation with errors when certain inputs are not provided and if provided bad status type when store in campaigns', function () {
    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.marketing.communications.campaigns.store'), [
        'status' => fake()->word(),
    ])
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('subject')
        ->assertJsonValidationErrorFor('marketing_template_id')
        ->assertJsonValidationErrorFor('marketing_event_id')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('customer_group_id')
        ->assertJsonValidationErrorFor('status')
        ->assertUnprocessable();
});

it('should store the newly created campaigns', function () {
    // Arrange.
    $marketingTemplate = Template::factory()->create();

    $event = Event::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    postJson(route('admin.marketing.communications.campaigns.store'), $data = [
        'name' => fake()->name(),
        'subject' => fake()->title(),
        'marketing_template_id' => $marketingTemplate->id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'customer_group_id' => rand(1, 3),
    ])
        ->assertRedirect(route('admin.marketing.communications.campaigns.index'))
        ->isRedirect();

    $this->assertModelWise([
        Campaign::class => [
            [
                'name' => $data['name'],
                'subject' => $data['subject'],
                'marketing_template_id' => $marketingTemplate->id,
                'marketing_event_id' => $event->id,
                'channel_id' => 1,
                'customer_group_id' => $data['customer_group_id'],
            ],
        ],
    ]);
});

it('should show the edit page of campaigns', function () {
    // Arrange.
    $campaign = Campaign::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    get(route('admin.marketing.communications.campaigns.edit', $campaign->id))
        ->assertOk()
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.edit.title'))
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.edit.back-btn'));
});

it('should fail the validation with errors when certain inputs are not provided when update in campaigns', function () {
    // Arrange.
    $campaign = Campaign::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    putJson(route('admin.marketing.communications.campaigns.update', $campaign->id))
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('subject')
        ->assertJsonValidationErrorFor('marketing_template_id')
        ->assertJsonValidationErrorFor('marketing_event_id')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('customer_group_id')
        ->assertUnprocessable();
});

it('should fail the validation with errors when certain inputs are not provided and if provided bad status type when update in campaigns', function () {
    // Arrange.
    $campaign = Campaign::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    putJson(route('admin.marketing.communications.campaigns.update', $campaign->id), [
        'status' => fake()->word(),
    ])
        ->assertJsonValidationErrorFor('name')
        ->assertJsonValidationErrorFor('subject')
        ->assertJsonValidationErrorFor('marketing_template_id')
        ->assertJsonValidationErrorFor('marketing_event_id')
        ->assertJsonValidationErrorFor('channel_id')
        ->assertJsonValidationErrorFor('customer_group_id')
        ->assertUnprocessable();
});

it('should update specified the campaigns', function () {
    // Arrange.
    $campaign = Campaign::factory()->create(['marketing_template_id' => Template::factory()->create()->id]);

    $event = Event::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    putJson(route('admin.marketing.communications.campaigns.edit', $campaign->id), $data = [
        'name' => $campaign->name,
        'subject' => fake()->title(),
        'marketing_template_id' => $campaign->marketing_template_id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'customer_group_id' => $customerGroupId = rand(1, 3),
    ])
        ->assertRedirect(route('admin.marketing.communications.campaigns.index'))
        ->isRedirect();

    $this->assertModelWise([
        Campaign::class => [
            [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'subject' => $data['subject'],
                'marketing_template_id' => $campaign->marketing_template_id,
                'marketing_event_id' => $event->id,
                'channel_id' => 1,
                'customer_group_id' => $customerGroupId,
            ],
        ],
    ]);
});

it('should delete the campaign', function () {
    // Arrange.
    $campaign = Campaign::factory()->create();

    // Act and Assert.
    $this->loginAsAdmin();

    deleteJson(route('admin.marketing.communications.campaigns.delete', $campaign->id))
        ->assertOk()
        ->assertSeeText(trans('admin::app.marketing.communications.campaigns.delete-success'));

    $this->assertDatabaseMissing('marketing_campaigns', [
        'id' => $campaign->id,
    ]);
});

// --- Tag filter controller tests ---

it('should store campaign with filter_by_tags enabled and persist pivot rows', function () {
    // Arrange.
    $template = Template::factory()->create(['status' => 'active']);
    $event = Event::factory()->create();
    $tag1 = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip']);
    $tag2 = SubscriberTag::create(['name' => 'Early', 'slug' => 'early']);

    $this->loginAsAdmin();

    // Act.
    postJson(route('admin.marketing.communications.campaigns.store'), [
        'name' => 'Tag Campaign',
        'subject' => 'Tag Subject',
        'marketing_template_id' => $template->id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'filter_by_tags' => 1,
        'subscriber_tag_ids' => [$tag1->id, $tag2->id],
    ])->assertRedirect(route('admin.marketing.communications.campaigns.index'));

    // Assert.
    $campaign = Campaign::latest()->first();

    expect($campaign->filter_by_tags)->toBeTrue();
    expect($campaign->subscriberTags->pluck('id')->sort()->values()->toArray())
        ->toEqual(collect([$tag1->id, $tag2->id])->sort()->values()->toArray());
});

it('should store campaign without filter_by_tags and leave pivot empty', function () {
    // Arrange.
    $template = Template::factory()->create(['status' => 'active']);
    $event = Event::factory()->create();

    $this->loginAsAdmin();

    // Act.
    postJson(route('admin.marketing.communications.campaigns.store'), [
        'name' => 'Group Campaign',
        'subject' => 'Group Subject',
        'marketing_template_id' => $template->id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'customer_group_id' => 2,
    ])->assertRedirect(route('admin.marketing.communications.campaigns.index'));

    // Assert.
    $campaign = Campaign::latest()->first();

    expect($campaign->filter_by_tags)->toBeFalse();
    expect($campaign->subscriberTags)->toBeEmpty();
});

it('should update campaign to enable tag filter and populate pivot', function () {
    // Arrange.
    $campaign = Campaign::factory()->create([
        'marketing_template_id' => Template::factory()->create(['status' => 'active'])->id,
        'filter_by_tags' => false,
        'customer_group_id' => 2,
    ]);
    $event = Event::factory()->create();
    $tag = SubscriberTag::create(['name' => 'Promo', 'slug' => 'promo']);

    $this->loginAsAdmin();

    // Act.
    putJson(route('admin.marketing.communications.campaigns.update', $campaign->id), [
        'name' => $campaign->name,
        'subject' => $campaign->subject,
        'marketing_template_id' => $campaign->marketing_template_id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'filter_by_tags' => 1,
        'subscriber_tag_ids' => [$tag->id],
    ])->assertRedirect(route('admin.marketing.communications.campaigns.index'));

    // Assert.
    $campaign->refresh();

    expect($campaign->filter_by_tags)->toBeTrue();
    expect($campaign->subscriberTags->pluck('id')->toArray())->toContain($tag->id);
});

it('should update campaign to disable tag filter and clear pivot', function () {
    // Arrange.
    $tag = SubscriberTag::create(['name' => 'Sale', 'slug' => 'sale']);
    $campaign = Campaign::factory()->create([
        'marketing_template_id' => Template::factory()->create(['status' => 'active'])->id,
        'filter_by_tags' => true,
        'customer_group_id' => 2,
    ]);
    $campaign->subscriberTags()->attach($tag->id);
    $event = Event::factory()->create();

    $this->loginAsAdmin();

    // Act.
    putJson(route('admin.marketing.communications.campaigns.update', $campaign->id), [
        'name' => $campaign->name,
        'subject' => $campaign->subject,
        'marketing_template_id' => $campaign->marketing_template_id,
        'marketing_event_id' => $event->id,
        'channel_id' => 1,
        'customer_group_id' => 2,
        // filter_by_tags not sent → false
    ])->assertRedirect(route('admin.marketing.communications.campaigns.index'));

    // Assert.
    $campaign->refresh();

    expect($campaign->filter_by_tags)->toBeFalse();
    expect($campaign->subscriberTags)->toBeEmpty();
});

it('should cascade delete pivot rows when campaign is deleted', function () {
    // Arrange.
    $tag = SubscriberTag::create(['name' => 'Delete Me', 'slug' => 'delete-me']);
    $campaign = Campaign::factory()->create();
    $campaign->subscriberTags()->attach($tag->id);

    $this->loginAsAdmin();

    // Act.
    deleteJson(route('admin.marketing.communications.campaigns.delete', $campaign->id))
        ->assertOk();

    // Assert.
    $this->assertDatabaseMissing('campaign_subscriber_tag', ['campaign_id' => $campaign->id]);
});
