<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KonfirmasiController;

// Route untuk Ionic: domain-kamu.com/api/konfirmasi-pesanan/{id}
Route::post('/konfirmasi-pesanan/{id}', [KonfirmasiController::class, 'store']);