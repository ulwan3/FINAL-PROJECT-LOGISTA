<?php

namespace App\Providers;

use App\Models\Barang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $totalStokSql = '(COALESCE(stok_gudang, 0) + COALESCE(stok_rak, 0))';

            $stokKritisNotif = Barang::select('barangs.*')
                ->selectRaw("$totalStokSql as total_stok_hitung")
                ->whereRaw("$totalStokSql <= 0")
                ->orderBy('nama_barang', 'asc')
                ->get();

            $stokMenipisNotif = Barang::select('barangs.*')
                ->selectRaw("$totalStokSql as total_stok_hitung")
                ->whereRaw("$totalStokSql > 0")
                ->whereRaw("$totalStokSql <= stok_minimum")
                ->orderBy('nama_barang', 'asc')
                ->get();

            $jumlahNotifStok = $stokKritisNotif->count() + $stokMenipisNotif->count();

            $view->with([
                'stokKritisNotif'  => $stokKritisNotif,
                'stokMenipisNotif' => $stokMenipisNotif,
                'jumlahNotifStok'  => $jumlahNotifStok,
            ]);
        });
    }
}