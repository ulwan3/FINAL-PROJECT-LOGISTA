<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InventoryOpsController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // Items (Barang)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    
    // Supplier
    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::put('/supplier/{id}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    
    // Inventory Ops
    Route::get('/ops', [InventoryOpsController::class, 'index']);
    
    // Alerts
    Route::get('/alerts', [AlertController::class, 'index']);
    
    // Audit Trail
    Route::get('/audit-trail', [AuditController::class, 'index'])->name('audit.trail');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
    
    // Settings Routes
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.updateProfile');
    Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.updatePassword');
    Route::put('/settings/operator/{id}/toggle-active', [SettingController::class, 'toggleOperatorActive'])->name('settings.operator.toggleActive');
    Route::delete('/settings/operator/{id}/delete', [SettingController::class, 'deleteOperator'])->name('settings.operator.delete');
    Route::put('/settings/operator/deactivate-all', [SettingController::class, 'deactivateAllOperators'])->name('settings.operator.deactivateAll');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    
    // Kategori
    Route::resource('kategori', KategoriController::class);
    
    // Transaksi & Pesanan
    Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/pesanan/riwayat', [TransaksiController::class, 'history'])->name('pesanan.history');
    
    // Riwayat Aktivitas
    Route::get('/riwayat-aktivitas', [DashboardController::class, 'riwayat'])->name('riwayat.index');
    Route::post('/riwayat/konfirmasi/{id}', [DashboardController::class, 'konfirmasi'])->name('riwayat.konfirmasi');
    
    // Export
    Route::get('/export-pdf', [DashboardController::class, 'exportPDF'])->name('export.pdf');
});