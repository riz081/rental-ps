<?php
// routes/web.php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings/calculate-price', [BookingController::class, 'calculatePrice'])->name('bookings.calculate-price');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{id}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
Route::post('/bookings/callback', [BookingController::class, 'callback'])->name('bookings.callback');
Route::get('/bookings/{id}/update-payment', [BookingController::class, 'updatePaymentStatus'])
    ->name('bookings.update-payment');