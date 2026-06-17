<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryOpsController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Ambil transaksi per jenis dengan pagination
        $transaksiMasuk = Transaksi::with(['barang', 'user'])
            ->where('jenis', 'barang_masuk')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_masuk');

        $transaksiKeluar = Transaksi::with(['barang', 'user'])
            ->where('jenis', 'barang_keluar')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_keluar');

        $transaksiMutasi = Transaksi::with(['barang', 'user'])
            ->where('jenis', 'mutasi')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_mutasi');

        // Statistik jumlah barang hari ini
        $totalMasuk = Transaksi::where('jenis', 'barang_masuk')
            ->whereDate('created_at', $today)
            ->sum('jumlah');

        $totalKeluar = Transaksi::where('jenis', 'barang_keluar')
            ->whereDate('created_at', $today)
            ->sum('jumlah');

        $totalMutasi = Transaksi::where('jenis', 'mutasi')
            ->whereDate('created_at', $today)
            ->sum('jumlah');

        // Statistik jumlah transaksi hari ini
        $countMasuk = Transaksi::where('jenis', 'barang_masuk')
            ->whereDate('created_at', $today)
            ->count();

        $countKeluar = Transaksi::where('jenis', 'barang_keluar')
            ->whereDate('created_at', $today)
            ->count();

        $countMutasi = Transaksi::where('jenis', 'mutasi')
            ->whereDate('created_at', $today)
            ->count();

        return view('ops.index', compact(
            'transaksiMasuk',
            'transaksiKeluar',
            'transaksiMutasi',
            'totalMasuk',
            'totalKeluar',
            'totalMutasi',
            'countMasuk',
            'countKeluar',
            'countMutasi'
        ));
    }
}