<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KonfirmasiController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FcmTokenController;

// Route untuk Ionic: domain-kamu.com/api/konfirmasi-pesanan/{id}
//Route::post('/konfirmasi-pesanan/{id}', [KonfirmasiController::class, 'store']);



Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications-count', [NotificationController::class, 'count']);

Route::post('/save-fcm-token', [FcmTokenController::class, 'store']);