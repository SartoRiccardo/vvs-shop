<?php

namespace Webkul\SubscriberTags\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscribersList extends \Webkul\Core\Models\SubscribersList
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(SubscriberTag::class, 'subscriber_tag', 'subscriber_id', 'tag_id')
            ->withPivot('created_at');
    }
}
