<?php

use App\Http\Controllers\Api\ChildPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Single Child Print Data
//Route::get('/print/child/{id}', [ChildPrintController::class, 'getSingleChildData'])
//    ->name('api.print.child');
//
//// Bulk/Batch Print Data
//Route::get('/print/batch', [ChildPrintController::class, 'getBatchData'])
//    ->name('api.print.batch');
Route::middleware(['auth'])->group(function () {

    Route::prefix('print/child/{id}')->controller(ChildPrintController::class)->group(function () {
            Route::get('monthly-monitoring', 'monthlyMonitoring')->name('print.child.monthly-monitoring');
            Route::get('profile', 'childProfile')->name('print.child.profile');
            Route::get('items-delivered', 'itemsDelivered')->name('print.child.items-delivered');
            Route::get('immunization-record', 'immunizationRecord')->name('print.child.immunization-record');
            Route::get('combined','combined')->name('print.child.combined');
        });
});
