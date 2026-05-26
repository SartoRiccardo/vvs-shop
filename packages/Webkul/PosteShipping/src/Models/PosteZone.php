<?php

namespace Webkul\PosteShipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosteZone extends Model
{
    protected $fillable = ['service_id', 'name', 'description'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(PosteService::class, 'service_id');
    }

    public function countryZones(): HasMany
    {
        return $this->hasMany(PosteCountryZone::class, 'zone_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PosteRate::class, 'zone_id');
    }
}
