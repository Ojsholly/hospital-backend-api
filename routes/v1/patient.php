<?php

use App\Http\Controllers\API\v1\Patient\PatientController;
use App\Http\Controllers\API\v1\User\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('patients', PatientController::class)->only('store');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::apiResource('patients.appointments', AppointmentController::class)->only('index', 'show', 'store');
});
