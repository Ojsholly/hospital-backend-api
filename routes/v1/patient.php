<?php

use App\Http\Controllers\API\v1\Patient\PatientController;
use Illuminate\Support\Facades\Route;

Route::apiResource('patients', PatientController::class)->only('store');
