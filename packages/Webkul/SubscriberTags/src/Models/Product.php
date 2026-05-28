<?php

namespace Webkul\SubscriberTags\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends \Webkul\Product\Models\Product
{
    public function subscriberTags(): BelongsToMany
    {
        return $this->belongsToMany(SubscriberTag::class, 'product_subscriber_tag', 'product_id', 'tag_id');
    }
}
