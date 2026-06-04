<?php

namespace Webkul\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Core\Models\ChannelProxy;
use Webkul\Customer\Models\CustomerGroupProxy;
use Webkul\Marketing\Contracts\Campaign as CampaignContract;
use Webkul\Marketing\Database\Factories\CampaignFactory;
use Webkul\SubscriberTags\Models\SubscriberTag;

class Campaign extends Model implements CampaignContract
{
    use HasFactory;

    /**
     * Define the table name for the model
     *
     * @var string
     */
    protected $table = 'marketing_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'status',
        'channel_id',
        'customer_group_id',
        'marketing_template_id',
        'spooling',
        'marketing_event_id',
        'filter_by_tags',
    ];

    protected $casts = [
        'filter_by_tags' => 'boolean',
    ];

    /**
     * Get the event
     */
    public function event()
    {
        return $this->belongsTo(EventProxy::modelClass(), 'marketing_event_id');
    }

    /**
     * Get the channel
     */
    public function channel()
    {
        return $this->belongsTo(ChannelProxy::modelClass(), 'channel_id');
    }

    /**
     * Get the customer group
     */
    public function customer_group()
    {
        return $this->belongsTo(CustomerGroupProxy::modelClass(), 'customer_group_id');
    }

    /**
     * Get the email template
     */
    public function email_template()
    {
        return $this->belongsTo(TemplateProxy::modelClass(), 'marketing_template_id');
    }

    public function subscriberTags(): BelongsToMany
    {
        return $this->belongsToMany(SubscriberTag::class, 'campaign_subscriber_tag', 'campaign_id', 'tag_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return CampaignFactory::new();
    }
}
