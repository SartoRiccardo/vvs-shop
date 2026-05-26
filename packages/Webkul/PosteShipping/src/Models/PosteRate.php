<?php

namespace Webkul\PosteShipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosteRate extends Model
{
    protected $fillable = ['zone_id', 'max_weight_kg', 'cost_eur'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(PosteZone::class, 'zone_id');
    }
}
