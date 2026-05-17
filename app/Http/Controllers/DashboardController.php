<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\PemesananBarang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBarang = Barang::count();
        $stokMenipis = Barang::whereColumn('stok', '<=', 'stok_minimum')->count();
        
        // --- LOGIKA KAPASITAS GUDANG ---
        $kapasitasMaksimal = 1000; 
        $totalStokSaatIni = Barang::sum('stok');
        $persentaseGudang = max(0, min(($totalStokSaatIni / $kapasitasMaksimal) * 100, 100));

        $today = Carbon::today();
        $transaksiHarianMasuk = Transaksi::where('jenis', 'masuk')->whereDate('created_at', $today)->count();
        $transaksiHarianKeluar = Transaksi::where('jenis', 'keluar')->whereDate('created_at', $today)->count();
        $totalTransaksiHarian = $transaksiHarianMasuk + $transaksiHarianKeluar;
        
        $recentActivities = PemesananBarang::with(['barang', 'user', 'supplier'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

        $days = collect(range(6, 0))->map(fn($i) => Carbon::now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn($date) => Carbon::parse($date)->format('D'));

       $stok_masuk = $days->map(function($date) {
            return \App\Models\PemesananBarang::whereDate('created_at', $date)
                ->where('status', 'diterima') 
                ->sum('jumlah_pesan') ?? 0;   
            });

       $stok_keluar = $days->map(function($date) {
            // Ganti ke model Transaksi dan filter jenis 'keluar'
            return \App\Models\Transaksi::whereDate('created_at', $date)
                ->where('jenis', 'keluar') 
                ->sum('jumlah') ?? 0;
        });

        $suppliers = \App\Models\Supplier::all();

        return view('dashboard', compact(
            'totalBarang', 
            'stokMenipis', 
            'transaksiHarianMasuk', 
            'transaksiHarianKeluar', 
            'totalTransaksiHarian',
            'recentActivities',
            'persentaseGudang',
            'labels',      
            'stok_masuk',  
            'stok_keluar',
            'suppliers'
        ));
    }

    public function getChartData(Request $request)
    {
        $filter = $request->get('filter', 'minggu'); // Default minggu jika kosong
        
        if ($filter === 'bulan') {
            // Ambil data 30 hari terakhir
            $range = range(29, 0);
            $formatLabel = 'd M'; // Contoh: 14 May
        } else {
            // Default 7 hari terakhir (Minggu Ini)
            $range = range(6, 0);
            $formatLabel = 'D'; // Contoh: Mon, Tue
        }

        $days = collect($range)->map(fn($i) => Carbon::now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn($date) => Carbon::parse($date)->format($formatLabel));

        $stok_masuk = $days->map(function($date) {
            return \App\Models\PemesananBarang::whereDate('created_at', $date)
                ->where('status', 'diterima') 
                ->sum('jumlah_pesan') ?? 0;   
        });

        $stok_keluar = $days->map(function($date) {
            return \App\Models\Transaksi::whereDate('created_at', $date)
                ->where('jenis', 'keluar') 
                ->sum('jumlah') ?? 0;
        });

        return response()->json([
            'labels' => $labels,
            'stok_masuk' => $stok_masuk,
            'stok_keluar' => $stok_keluar
        ]);
    }

    public function riwayat()
    {
        $semuaAktivitas = PemesananBarang::with(['barang', 'user', 'supplier'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        return view('riwayat.riwayat', compact('semuaAktivitas'));
    }

    public function konfirmasi(Request $request, $id)
    {
        // 1. Validasi input agar data yang masuk konsisten
        $request->validate([
            'status' => 'required|in:sampai,tidak_sesuai',
            'qty_diterima' => 'required|integer|min:0',
            'catatan' => 'nullable|string'
        ]);

        $pesanan = PemesananBarang::findOrFail($id);

        // 2. Gunakan Transaction agar sinkronisasi data aman (All or Nothing)
        DB::transaction(function () use ($request, $pesanan) {
            
            // Update status di tabel pemesanan
            $pesanan->update([
                'status' => $request->status,
                'qty_diterima' => $request->qty_diterima,
                'catatan' => $request->catatan,
                'verified_at' => now(),
            ]);

            // 3. LOGIKA OTOMATIS TAMBAH STOK
            // Hanya diproses jika barang benar-benar sampai
            if ($request->status == 'sampai' && $request->qty_diterima > 0) {
                $barang = Barang::find($pesanan->barang_id);
                
                if ($barang) {
                    // Update stok utama di tabel Barang
                    $barang->increment('stok', $request->qty_diterima);
                    
                    // Catat ke tabel Transaksi sebagai bukti arus barang masuk
                    Transaksi::create([
                        'barang_id' => $pesanan->barang_id,
                        'user_id'   => auth()->id(),
                        'jenis'     => 'masuk',
                        'jumlah'    => $request->qty_diterima,
                        'keterangan'=> "Penerimaan pesanan ID #{$pesanan->id}"
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Barang telah diterima dan stok gudang otomatis diperbarui!');
    }

    public function exportPDF()
    {
        // Mengambil data sesuai indikator di image_9232e2.png
        $data = [
            'tanggal' => now()->format('d F Y, H:i'),
            'total_barang' => \App\Models\Barang::count(),
            'stok_menipis' => \App\Models\Barang::whereColumn('stok', '<=', 'stok_minimum')->count(),
            'kapasitas' => 22, // Contoh statis sesuai gambar, bisa diganti logika dinamis
            'aktivitas' => \App\Models\PemesananBarang::with('barang')->latest()->take(10)->get()
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.dashboard_pdf', $data);
        
        // Download file dengan nama otomatis
        return $pdf->download('Laporan_Gudang_Logista.pdf');
    }
}