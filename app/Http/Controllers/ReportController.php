<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Data untuk card statistik (Total Barang Masuk/Keluar Bulan Ini)
        $masuk = Transaksi::where('jenis', 'barang_masuk')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');
            
        $keluar = Transaksi::where('jenis', 'barang_keluar')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');
        
        // Hitung total transaksi (untuk cek apakah ada data)
        $totalTransaksi = Transaksi::count();
        
        // Data untuk grafik (4 minggu terakhir)
        $chartData = $this->getChartData();
        
        // DATA REAL: Top 5 Barang Paling Sering Keluar (Bulan Ini)
        $topBarangKeluar = Transaksi::where('jenis', 'barang_keluar')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('barang_id', DB::raw('SUM(jumlah) as total_keluar'))
            ->with('barang')
            ->groupBy('barang_id')
            ->orderBy('total_keluar', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return (object) [
                    'nama_barang' => $item->barang->nama_barang ?? 'Barang Tidak Dikenal',
                    'total_keluar' => $item->total_keluar,
                    'satuan' => $item->barang->satuan ?? 'pcs'
                ];
            });
        
        return view('reports.index', compact(
            'masuk', 
            'keluar', 
            'totalTransaksi', 
            'chartData',
            'topBarangKeluar'
        ));
    }
    
    private function getChartData()
    {
        $data = [
            'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            'masuk' => [0, 0, 0, 0],
            'keluar' => [0, 0, 0, 0]
        ];
        
        if (Transaksi::count() == 0) {
            return $data;
        }
        
        for ($i = 3; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = now()->subWeeks($i)->endOfWeek();
            
            $masuk = Transaksi::where('jenis', 'barang_masuk')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('jumlah');
                
            $keluar = Transaksi::where('jenis', 'barang_keluar')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('jumlah');
            
            $data['masuk'][3 - $i] = $masuk ?: 0;
            $data['keluar'][3 - $i] = $keluar ?: 0;
        }
        
        return $data;
    }
}