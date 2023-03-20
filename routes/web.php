<?php

use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\Misc\VerifyAppointmentPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify');

Route::view('reset-password', 'auth.reset-password')->name('password.reset');

Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('appointments/{appointment_id}/verify-payment/{gateway}', VerifyAppointmentPaymentController::class)->name('appointments.verify-payment');
