<?php

namespace Webkul\FedExShipping\Models;

use Illuminate\Database\Eloquent\Model;

class FedExRate extends Model
{
    protected $table = 'fedex_ficp_rates';

    public $timestamps = false;

    protected $casts = [
        'weight_max'   => 'float',
        'flat_rate'    => 'float',
        'per_kg_rate'  => 'float',
    ];
}
