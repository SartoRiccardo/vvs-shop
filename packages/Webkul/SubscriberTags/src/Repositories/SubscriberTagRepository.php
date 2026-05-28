<?php

namespace Webkul\SubscriberTags\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\SubscriberTags\Models\Product;
use Webkul\SubscriberTags\Models\SubscribersList;
use Webkul\SubscriberTags\Models\SubscriberTag;

class SubscriberTagRepository extends Repository
{
    public function model(): string
    {
        return SubscriberTag::class;
    }

    public function syncSubscriberTags(int $subscriberId, array $tagIds): void
    {
        SubscribersList::findOrFail($subscriberId)->tags()->sync($tagIds);
    }

    public function attachTagsToSubscriberByEmail(string $email, array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        $subscriber = SubscribersList::where('email', $email)
            ->where('is_subscribed', true)
            ->first();

        if (! $subscriber) {
            return;
        }

        $subscriber->tags()->syncWithoutDetaching($tagIds);
    }

    public function getTagsForSubscriber(int $subscriberId): \Illuminate\Support\Collection
    {
        return SubscribersList::findOrFail($subscriberId)->tags;
    }

    public function getTagsForProduct(int $productId): \Illuminate\Support\Collection
    {
        return Product::findOrFail($productId)->subscriberTags;
    }

    public function syncProductTags(int $productId, array $tagIds): void
    {
        Product::findOrFail($productId)->subscriberTags()->sync($tagIds);
    }
}
