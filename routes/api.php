<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KonfirmasiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\LogistaApiController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\SettingController;

// =====================================
// ROUTE PUBLIC (TIDAK PERLU LOGIN)
// =====================================
Route::get('/', [LogistaApiController::class, 'api']);

// Autentikasi & Password
Route::post('/register', [LogistaApiController::class, 'register']);
Route::post('/login', [LogistaApiController::class, 'login']);
Route::post('/login-operator', [AuthController::class, 'apiLogin']);
Route::post('/lupa-password', [LogistaApiController::class, 'lupaPassword']);
Route::post('/masukkan-otp', [LogistaApiController::class, 'masukkanOtp']);
Route::post('/reset-password', [LogistaApiController::class, 'resetPassword']);

// =====================================
// ROUTE OPERATOR
// =====================================
Route::put('/update-activity', [AuthController::class, 'apiUpdateActivity']);
Route::get('/check-status', [AuthController::class, 'apiCheckStatus']);
Route::post('/logout-operator', [AuthController::class, 'apiLogout']);

// =====================================
// ROUTE NOTIFICATION & FCM TOKEN
// =====================================
Route::get('/notifications', [LogistaApiController::class, 'notifications']);
Route::get('/notifications-count', [LogistaApiController::class, 'notificationsCount']);
Route::post('/save-fcm-token', [FcmTokenController::class, 'store']);

// =====================================
// ROUTE BARANG
// =====================================
Route::get('/barang', [LogistaApiController::class, 'barang']);
Route::get('/barang/{id}', [LogistaApiController::class, 'detailBarang']);

// =====================================
// ROUTE PEMESANAN
// =====================================
Route::get('/pemesanan', [LogistaApiController::class, 'pemesanan']);
Route::post('/pemesanan', [LogistaApiController::class, 'tambahPemesanan']);

// Konfirmasi Pesanan (dari Ionic) - Sementara dikomentari
// Route::post('/konfirmasi-pesanan/{id}', [KonfirmasiController::class, 'store']);

// =====================================
// ROUTE INVENTORY & STOCK
// =====================================
Route::post('/verifikasi-barang', [LogistaApiController::class, 'verifikasiBarang']);
Route::post('/barang-keluar', [LogistaApiController::class, 'barangKeluar']);
Route::post('/mutasi-barang', [LogistaApiController::class, 'mutasiBarang']);
Route::get('/dashboard-stock', [LogistaApiController::class, 'dashboardStock']);
Route::get('/inventory-history', [LogistaApiController::class, 'inventoryHistory']);
Route::get('/stock-line-chart', [LogistaApiController::class, 'stockLineChart']);

Route::put('/update-activity', [SettingController::class, 'updateActivity']);
Route::get('/check-status', [SettingController::class, 'checkStatus']);
Route::post('/logout-operator', [SettingController::class, 'logoutOperator']);