<?php

use Webkul\Admin\Http\Controllers\Media\MediaLibraryController;

/**
 * Media library routes.
 */
Route::controller(MediaLibraryController::class)->prefix('media')->group(function () {
    Route::get('/', 'index')->name('admin.media.index');

    Route::post('upload', 'store')->name('admin.media.store');

    Route::delete('{name}', 'destroy')->name('admin.media.delete');
});
