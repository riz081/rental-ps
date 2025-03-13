<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::post('/midtrans-callback', [BookingController::class, 'callback']);