<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Data untuk card statistik (Total Barang Masuk/Keluar Bulan Ini)
        $masuk = Transaksi::where('jenis', 'masuk')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');
            
        $keluar = Transaksi::where('jenis', 'keluar')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');
        
        // Data untuk grafik (4 minggu terakhir)
        $chartData = $this->getChartData();
        
        return view('reports.index', compact('masuk', 'keluar', 'chartData'));
    }
    
    private function getChartData()
    {
        $data = [
            'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            'masuk' => [0, 0, 0, 0],
            'keluar' => [0, 0, 0, 0]
        ];
        
        // Jika tidak ada data sama sekali, kembalikan array kosong (0)
        if (Transaksi::count() == 0) {
            return $data;
        }
        
        // Hitung per minggu (4 minggu terakhir)
        for ($i = 3; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = now()->subWeeks($i)->endOfWeek();
            
            $masuk = Transaksi::where('jenis', 'masuk')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('jumlah');
                
            $keluar = Transaksi::where('jenis', 'keluar')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('jumlah');
            
            $data['masuk'][3 - $i] = $masuk ?: 0;
            $data['keluar'][3 - $i] = $keluar ?: 0;
        }
        
        return $data;
    }
}