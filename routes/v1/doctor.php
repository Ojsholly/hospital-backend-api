<?php

use App\Http\Controllers\API\v1\Doctor\AppointmentController;

Route::middleware(['auth:sanctum', 'verified', 'roles:doctor'])->group(function () {
    Route::apiResource('doctors.appointments', AppointmentController::class)->only(['index', 'show', 'update']);
});
