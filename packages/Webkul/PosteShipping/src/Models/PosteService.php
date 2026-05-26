<?php

namespace Webkul\PosteShipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosteService extends Model
{
    protected $fillable = ['name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function zones(): HasMany
    {
        return $this->hasMany(PosteZone::class, 'service_id');
    }
}
