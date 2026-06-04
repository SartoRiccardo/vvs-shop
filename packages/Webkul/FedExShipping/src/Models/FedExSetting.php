<?php

namespace Webkul\FedExShipping\Models;

use Illuminate\Database\Eloquent\Model;

class FedExSetting extends Model
{
    protected $table = 'fedex_ficp_settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::find($key)?->value ?? $default;
    }

    public static function all($columns = ['*'])
    {
        return parent::all($columns)->pluck('value', 'key');
    }
}
