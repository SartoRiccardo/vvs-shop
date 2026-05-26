<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;
use Webkul\PosteShipping\Http\Controllers\ServiceController;
use Webkul\PosteShipping\Http\Controllers\ZoneController;
use Webkul\PosteShipping\Http\Controllers\RateController;

Route::group(['middleware' => ['web', 'admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url').'/poste'], function () {
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('services.zones', ZoneController::class)->except(['show']);
    Route::resource('services.zones.rates', RateController::class)->except(['show', 'index']);
});
