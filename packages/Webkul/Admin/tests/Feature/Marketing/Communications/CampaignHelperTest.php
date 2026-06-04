<?php

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\SubscribersList;
use Webkul\Marketing\Helpers\Campaign as CampaignHelper;
use Webkul\Marketing\Models\Campaign;
use Webkul\Marketing\Models\Template;
use Webkul\SubscriberTags\Models\SubscribersList as TaggedSubscribersList;
use Webkul\SubscriberTags\Models\SubscriberTag;

// Helper: create a subscriber and optionally attach a tag to them.
function makeSubscriber(array $attrs = [], ?SubscriberTag $tag = null): SubscribersList
{
    $subscriber = SubscribersList::factory()->create($attrs);

    if ($tag) {
        DB::table('subscriber_tag')->insert([
            'subscriber_id' => $subscriber->id,
            'tag_id'        => $tag->id,
        ]);
    }

    return $subscriber;
}

// Helper: create a campaign with filter_by_tags and optional tags attached.
function makeCampaign(bool $filterByTags, array $tags = [], array $attrs = []): Campaign
{
    $campaign = Campaign::factory()->create(array_merge([
        'marketing_template_id' => Template::factory()->create(['status' => 'active'])->id,
        'filter_by_tags'        => $filterByTags,
        'customer_group_id'     => 1, // guest group
    ], $attrs));

    foreach ($tags as $tag) {
        $campaign->subscriberTags()->attach($tag->id);
    }

    return $campaign->fresh(['subscriberTags']);
}

// --- Tests ---

it('filter_by_tags=false uses group logic and ignores attached tags', function () {
    $tag = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip-a']);

    // Subscriber in guest group (customer_id=null), is_subscribed, NO tag.
    $included = makeSubscriber(['customer_id' => null, 'is_subscribed' => 1]);

    // Campaign for guest group with a tag attached but filter OFF.
    $campaign = makeCampaign(false, [$tag]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->toContain($included->email);
});

it('filter_by_tags=true includes subscriber with matching tag', function () {
    $tag = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip-b']);
    $subscriber = makeSubscriber(['is_subscribed' => 1], $tag);
    $campaign = makeCampaign(true, [$tag]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->toContain($subscriber->email);
});

it('filter_by_tags=true excludes subscriber without matching tag', function () {
    $tag = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip-c']);
    $otherTag = SubscriberTag::create(['name' => 'Other', 'slug' => 'other-c']);

    $excluded = makeSubscriber(['is_subscribed' => 1], $otherTag); // wrong tag
    $campaign = makeCampaign(true, [$tag]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->not->toContain($excluded->email);
});

it('filter_by_tags=true includes subscriber who has at least one of multiple tags (OR logic)', function () {
    $tag1 = SubscriberTag::create(['name' => 'Alpha', 'slug' => 'alpha-d']);
    $tag2 = SubscriberTag::create(['name' => 'Beta', 'slug' => 'beta-d']);

    // Subscriber only has tag2, campaign requires tag1 OR tag2.
    $subscriber = makeSubscriber(['is_subscribed' => 1], $tag2);
    $campaign = makeCampaign(true, [$tag1, $tag2]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->toContain($subscriber->email);
});

it('filter_by_tags=true with no tags attached returns empty list', function () {
    makeSubscriber(['is_subscribed' => 1]); // active subscriber, irrelevant

    $campaign = makeCampaign(true, []); // no tags

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->toBeEmpty();
});

it('filter_by_tags=true excludes unsubscribed subscriber even with matching tag', function () {
    $tag = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip-f']);
    $unsubscribed = makeSubscriber(['is_subscribed' => 0], $tag);
    $campaign = makeCampaign(true, [$tag]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->not->toContain($unsubscribed->email);
});

it('filter_by_tags=true includes subscriber not in campaign customer group if they have the tag', function () {
    $tag = SubscriberTag::create(['name' => 'VIP', 'slug' => 'vip-g']);

    // Campaign targets "general" group (customer_group_id=2).
    // Subscriber has no customer account (customer_id=null) so would never appear in group logic.
    $subscriber = makeSubscriber(['customer_id' => null, 'is_subscribed' => 1], $tag);
    $campaign = makeCampaign(true, [$tag], ['customer_group_id' => 2]);

    $emails = app(CampaignHelper::class)->getEmailAddresses($campaign);

    expect($emails)->toContain($subscriber->email);
});
