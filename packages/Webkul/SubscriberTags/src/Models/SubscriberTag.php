<?php

namespace Webkul\SubscriberTags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Core\Models\SubscribersList;

class SubscriberTag extends Model
{
    protected $fillable = ['name', 'slug', 'auto_assign_on_purchase'];

    protected $casts = ['auto_assign_on_purchase' => 'boolean'];

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(SubscribersList::class, 'subscriber_tag', 'tag_id', 'subscriber_id')
            ->withPivot('created_at');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(\Webkul\Product\Models\Product::class, 'product_subscriber_tag', 'tag_id', 'product_id');
    }
}
