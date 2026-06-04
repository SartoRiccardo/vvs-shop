<?php

namespace Webkul\FedExShipping\Models;

use Illuminate\Database\Eloquent\Model;

class FedExDemandCountry extends Model
{
    protected $table = 'fedex_ficp_demand_countries';

    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(FedExDemandGroup::class, 'group_id');
    }
}
