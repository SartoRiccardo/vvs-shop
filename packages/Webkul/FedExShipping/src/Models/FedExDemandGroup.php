<?php

namespace Webkul\FedExShipping\Models;

use Illuminate\Database\Eloquent\Model;

class FedExDemandGroup extends Model
{
    protected $table = 'fedex_ficp_demand_groups';

    public $timestamps = false;

    protected $casts = [
        'base_rate'    => 'float',
        'per_kg_rate'  => 'float',
    ];

    public function countries()
    {
        return $this->hasMany(FedExDemandCountry::class, 'group_id');
    }
}
