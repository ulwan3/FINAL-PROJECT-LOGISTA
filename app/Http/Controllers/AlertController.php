<?php

namespace App\Http\Controllers;

use App\Models\Barang;

class AlertController extends Controller
{
    public function index()
    {
        $totalStokSql = '(COALESCE(stok_gudang, 0) + COALESCE(stok_rak, 0))';

        // Status kritis / kosong = stok gudang + stok rak <= 0
        $kritis = Barang::select('barangs.*')
            ->selectRaw("$totalStokSql as total_stok_hitung")
            ->whereRaw("$totalStokSql <= 0")
            ->get();

        // Status menipis = total stok > 0 dan <= stok minimum
        $menipis = Barang::select('barangs.*')
            ->selectRaw("$totalStokSql as total_stok_hitung")
            ->whereRaw("$totalStokSql > 0")
            ->whereRaw("$totalStokSql <= stok_minimum")
            ->get();

        return view('alerts.index', compact('kritis', 'menipis'));
    }
}