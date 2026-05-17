<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryOpsController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\kategoriController;
use App\Http\Controllers\TransaksiController;

Route::get('/', function () {
    // Redirect ke dashboard jika sudah login
    if (auth()->check()) return redirect('/dashboard');
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/ops', [InventoryOpsController::class, 'index']);
    Route::get('/alerts', [AlertController::class, 'index']);
    Route::get('/audit-trail', [AuditController::class, 'index']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::resource('kategori', KategoriController::class);
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/pesanan/riwayat', [TransaksiController::class, 'history'])->name('pesanan.history');
    Route::get('/riwayat-aktivitas', [App\Http\Controllers\DashboardController::class, 'riwayat'])->name('riwayat.index');
    Route::post('/riwayat/konfirmasi/{id}', [DashboardController::class, 'konfirmasi'])->name('riwayat.konfirmasi');
    Route::get('/export-pdf', [DashboardController::class, 'exportPDF'])->name('export.pdf');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
});
