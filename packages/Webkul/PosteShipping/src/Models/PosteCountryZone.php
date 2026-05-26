<?php

namespace Webkul\PosteShipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosteCountryZone extends Model
{
    protected $fillable = ['zone_id', 'country_code'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(PosteZone::class, 'zone_id');
    }
}
