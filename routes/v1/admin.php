<?php

use App\Http\Controllers\API\v1\Admin\DoctorController;

Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'roles:admin,super-admin'])->group(function () {
    Route::apiResource('doctors', DoctorController::class);
});
