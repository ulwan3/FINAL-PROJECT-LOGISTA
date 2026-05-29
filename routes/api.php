<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KonfirmasiController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FcmTokenController;

use App\Http\Controllers\LogistaApiController;

// Route untuk Ionic: domain-kamu.com/api/konfirmasi-pesanan/{id}
//Route::post('/konfirmasi-pesanan/{id}', [KonfirmasiController::class, 'store']);



Route::get('/', [LogistaApiController::class, 'api']);

Route::post('/register', [LogistaApiController::class, 'register']);
Route::post('/login', [LogistaApiController::class, 'login']);
Route::post('/lupa-password', [LogistaApiController::class, 'lupaPassword']);
Route::post('/masukkan-otp', [LogistaApiController::class, 'masukkanOtp']);
Route::post('/reset-password', [LogistaApiController::class, 'resetPassword']);

Route::get('/barang', [LogistaApiController::class, 'barang']);
Route::get('/barang/{id}', [LogistaApiController::class, 'detailBarang']);

Route::get('/pemesanan', [LogistaApiController::class, 'pemesanan']);
Route::post('/pemesanan', [LogistaApiController::class, 'tambahPemesanan']);

Route::post('/verifikasi-barang', [LogistaApiController::class, 'verifikasiBarang']);
Route::post('/barang-keluar', [LogistaApiController::class, 'barangKeluar']);
Route::post('/mutasi-barang', [LogistaApiController::class, 'mutasiBarang']);

Route::get('/dashboard-stock', [LogistaApiController::class, 'dashboardStock']);
Route::get('/inventory-history', [LogistaApiController::class, 'inventoryHistory']);
Route::get('/stock-line-chart', [LogistaApiController::class, 'stockLineChart']);

Route::get('/notifications', [LogistaApiController::class, 'notifications']);
Route::get('/notifications-count', [LogistaApiController::class, 'notificationsCount']);

Route::post('/save-fcm-token', [FcmTokenController::class, 'store']);