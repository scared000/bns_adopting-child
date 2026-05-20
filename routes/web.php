<?php

use App\Http\Controllers\Api\ChildPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get('print/child/batch/monthly-monitoring', [ChildPrintController::class, 'batchMonthlyMonitoring'])->name('print.child.batch.monthly-monitoring');
    Route::prefix('print/child/{id}')->where(['id' => '[0-9]+'])->controller(ChildPrintController::class)
        ->group(function () {
            Route::get('monthly-monitoring', 'monthlyMonitoring')->name('print.child.monthly-monitoring');
            Route::get('profile', 'childProfile')->name('print.child.profile');
            Route::get('items-delivered', 'itemsDelivered')->name('print.child.items-delivered');
            Route::get('immunization-record', 'immunizationRecord')->name('print.child.immunization-record');
            Route::get('combined', 'combined')->name('print.child.combined');
        });
});
