<?php

use Illuminate\Support\Facades\Route;
use Webkul\SubscriberTags\Http\Controllers\ProductTagController;
use Webkul\SubscriberTags\Http\Controllers\SubscriberTagController;
use Webkul\SubscriberTags\Http\Controllers\TagController;

Route::prefix('admin/marketing/communications')->middleware(['web', 'admin'])->group(function () {
    // Tag CRUD
    Route::controller(TagController::class)->prefix('subscriber-tags')->group(function () {
        Route::get('', 'index')->name('admin.marketing.communications.subscriber_tags.index');
        Route::get('all', 'all')->name('admin.marketing.communications.subscriber_tags.all');
        Route::post('', 'store')->name('admin.marketing.communications.subscriber_tags.store');
        Route::get('{id}', 'edit')->name('admin.marketing.communications.subscriber_tags.edit');
        Route::put('{id}', 'update')->name('admin.marketing.communications.subscriber_tags.update');
        Route::delete('{id}', 'destroy')->name('admin.marketing.communications.subscriber_tags.destroy');
    });

    // Subscriber <-> tags
    Route::controller(SubscriberTagController::class)->prefix('subscribers/{subscriberId}/tags')->group(function () {
        Route::get('', 'index')->name('admin.marketing.communications.subscriber_tags.subscriber.index');
        Route::put('', 'update')->name('admin.marketing.communications.subscriber_tags.subscriber.update');
    });
});

// Product <-> tags (nested under catalog for clarity)
Route::prefix('admin/catalog/products/{productId}/subscriber-tags')->middleware(['web', 'admin'])->controller(ProductTagController::class)->group(function () {
    Route::get('', 'index')->name('admin.catalog.products.subscriber_tags.index');
    Route::put('', 'update')->name('admin.catalog.products.subscriber_tags.update');
});
