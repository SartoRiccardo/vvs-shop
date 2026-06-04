<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;
use Webkul\FedExShipping\Http\Controllers\ZoneController;
use Webkul\FedExShipping\Http\Controllers\RateController;

Route::group([
    'middleware' => ['web', 'admin', NoCacheMiddleware::class],
    'prefix'     => config('app.admin_url').'/fedex',
    'as'         => 'fedex.',
], function () {
    Route::resource('zones', ZoneController::class)
        ->only(['index', 'edit', 'update'])
        ->parameters(['zones' => 'zoneCode']);

    Route::resource('zones.rates', RateController::class)
        ->except(['show', 'index'])
        ->parameters(['zones' => 'zoneCode']);
});
