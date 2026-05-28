<?php

namespace Webkul\SubscriberTags\Listeners;

use Webkul\SubscriberTags\Models\SubscriberTag;
use Webkul\SubscriberTags\Repositories\SubscriberTagRepository;

class AssignTagsOnPurchase
{
    public function __construct(protected SubscriberTagRepository $tagRepository) {}

    public function handle($order): void
    {
        if (! $order->customer_email) {
            return;
        }

        $productIds = $order->items->pluck('product_id')->unique()->toArray();

        $tagIds = SubscriberTag::query()
            ->where('auto_assign_on_purchase', true)
            ->orWhereHas('products', fn ($q) => $q->whereIn('products.id', $productIds))
            ->pluck('id')
            ->toArray();

        $this->tagRepository->attachTagsToSubscriberByEmail($order->customer_email, $tagIds);
    }
}
