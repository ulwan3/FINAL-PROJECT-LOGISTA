<?php

namespace App\Http\Controllers;

use App\Models\Barang;
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
            ->where('jenis', 'masuk')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_masuk');

        $transaksiKeluar = Transaksi::with(['barang', 'user'])
            ->where('jenis', 'keluar')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_keluar');

        $transaksiMutasi = Transaksi::with(['barang', 'user'])
            ->where('jenis', 'mutasi')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_mutasi');

        // Statistik hari ini
        $totalMasuk  = Transaksi::where('jenis', 'masuk')->whereDate('created_at', $today)->sum('jumlah');
        $totalKeluar = Transaksi::where('jenis', 'keluar')->whereDate('created_at', $today)->sum('jumlah');
        $countMasuk  = Transaksi::where('jenis', 'masuk')->whereDate('created_at', $today)->count();
        $countKeluar = Transaksi::where('jenis', 'keluar')->whereDate('created_at', $today)->count();
        $countMutasi = Transaksi::where('jenis', 'mutasi')->whereDate('created_at', $today)->count();

        return view('ops.index', compact(
            'transaksiMasuk',
            'transaksiKeluar',
            'transaksiMutasi',
            'totalMasuk',
            'totalKeluar',
            'countMasuk',
            'countKeluar',
            'countMutasi'
        ));
    }
}
