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

    private function totalStokSql()
    {
        return '(COALESCE(barangs.stok_gudang, 0) + COALESCE(barangs.stok_rak, 0))';
    }

    public function index()
    {
        $totalStokSql = $this->totalStokSql();

        $totalBarang = Barang::count();

        // Hitung stok menipis berdasarkan gabungan gudang & rak
        $stokMenipis = Barang::whereRaw("$totalStokSql <= barangs.stok_minimum")->count();

        // --- LOGIKA KAPASITAS GUDANG ---
        $kapasitasMaksimal = 5000; 

        // Jumlahkan seluruh isi gudang dan rak dari database
        $totalStokSaatIni = Barang::selectRaw("$totalStokSql as hitung_stok")
                                ->get()
                                ->sum('hitung_stok');

        $persentaseGudang = max(0, min(($totalStokSaatIni / $kapasitasMaksimal) * 100, 100));

        $today = Carbon::today();
        $transaksiHarianMasuk = Transaksi::where('jenis', 'barang_masuk')->whereDate('created_at', $today)->count();
        $transaksiHarianKeluar = Transaksi::where('jenis', 'barang_keluar')->whereDate('created_at', $today)->count();
        $totalTransaksiHarian = $transaksiHarianMasuk + $transaksiHarianKeluar;
        
        $recentActivities = PemesananBarang::with(['barang', 'user', 'supplier'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

        $days = collect(range(6, 0))->map(fn($i) => Carbon::now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn($date) => Carbon::parse($date)->format('D'));

        $stok_masuk = $days->map(function($date) {
            return PemesananBarang::whereDate('created_at', $date)
                ->where('status', 'diterima') 
                ->sum('jumlah_pesan') ?? 0;   
        });

        $stok_keluar = $days->map(function($date) {
            return Transaksi::whereDate('created_at', $date)
                ->where('jenis', 'keluar') 
                ->sum('jumlah') ?? 0;
        });

        $suppliers = \App\Models\Supplier::all();

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();

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
            'suppliers',
            'barangs',
            'totalStokSaatIni',
            'kapasitasMaksimal'
        ));
    }

    public function getChartData(Request $request)
    {
        $filter = $request->get('filter', 'minggu');
        
        if ($filter === 'bulan') {
            $range = range(29, 0);
            $formatLabel = 'd M';
        } else {
            $range = range(6, 0);
            $formatLabel = 'D';
        }

        $days = collect($range)->map(fn($i) => Carbon::now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn($date) => Carbon::parse($date)->format($formatLabel));

        $stok_masuk = $days->map(function($date) {
            return PemesananBarang::whereDate('created_at', $date)
                ->where('status', 'diterima') 
                ->sum('jumlah_pesan') ?? 0;   
        });

        $stok_keluar = $days->map(function($date) {
            return Transaksi::whereDate('created_at', $date)
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
        $request->validate([
            'status' => 'required|in:sampai,tidak_sesuai',
            'qty_diterima' => 'required|integer|min:0',
            'catatan' => 'nullable|string'
        ]);

        $pesanan = PemesananBarang::findOrFail($id);

        DB::transaction(function () use ($request, $pesanan) {
            
            $pesanan->update([
                'status' => $request->status,
                'qty_diterima' => $request->qty_diterima,
                'catatan' => $request->catatan,
                'verified_at' => now(),
            ]);

            if ($request->status == 'sampai' && $request->qty_diterima > 0) {
                $barang = Barang::find($pesanan->barang_id);
                
                if ($barang) {
                    $barang->increment('stok_gudang', $request->qty_diterima);

                    $totalStokBaru = ($barang->stok_gudang ?? 0) + ($barang->stok_rak ?? 0);
                    $barang->update([
                        'stok' => $totalStokBaru,
                        'total_stok' => $totalStokBaru
                    ]);
                    
                    Transaksi::create([
                        'barang_id' => $pesanan->barang_id,
                        'user_id'   => auth()->id(),
                        'jenis'     => 'barang_masuk',
                        'jumlah'    => $request->qty_diterima,
                        'keterangan'=> "Penerimaan pesanan ID #{$pesanan->id}"
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Barang telah diterima dan stok gudang otomatis diperbarui!');
    }

    /**
     * EXPORT PDF - VERSI FINAL (LENGKAP DENGAN STOK MASUK & KELUAR)
     */
    public function exportPDF()
    {
        $totalStokSql = $this->totalStokSql();
        
        // Ambil data barang dengan JOIN ke tabel kategoris
        $barang = Barang::leftJoin('kategoris', 'barangs.kategori_id', '=', 'kategoris.id')
            ->select(
                'barangs.*',
                'kategoris.nama as kategori_nama'
            )
            ->get();
        
        // Hitung stok aktual dan statistik untuk setiap barang
        foreach ($barang as $item) {
            // Stok aktual = stok_gudang + stok_rak
            $item->stok = ($item->stok_gudang ?? 0) + ($item->stok_rak ?? 0);
            
            // Hitung total stok masuk dari transaksi (jenis: barang_masuk)
            $masuk = Transaksi::where('barang_id', $item->id)
                ->where('jenis', 'barang_masuk')
                ->sum('jumlah');
            $item->total_stok_masuk = $masuk ?? 0;
            
            // Hitung total stok keluar dari transaksi (jenis: barang_keluar)
            $keluar = Transaksi::where('barang_id', $item->id)
                ->where('jenis', 'barang_keluar')
                ->sum('jumlah');
            $item->total_stok_keluar = $keluar ?? 0;
            
            // Jika kategori_nama masih kosong (fallback)
            if (empty($item->kategori_nama)) {
                $item->kategori_nama = '-';
            }
        }
        
        // Hitung total stok keseluruhan
        $total_stok = $barang->sum('stok');
        
        // Hitung stok habis (stok = 0)
        $stok_habis = $barang->filter(fn($item) => $item->stok <= 0)->count();
        
        // Hitung stok menipis (stok > 0 dan <= stok_minimum)
        $stok_menipis = Barang::whereRaw("$totalStokSql <= barangs.stok_minimum")
            ->whereRaw("$totalStokSql > 0")
            ->count();
        
        // Siapkan data untuk view PDF
        $data = [
            'tanggal'       => now()->format('d F Y, H:i'),
            'total_stok'    => $total_stok,
            'total_barang'  => Barang::count(),
            'stok_menipis'  => $stok_menipis,
            'stok_habis'    => $stok_habis,
            'barang'        => $barang,
        ];

        // Generate dan download PDF
        $pdf = Pdf::loadView('exports.dashboard_pdf', $data);
        return $pdf->download('Laporan_Gudang_Logista.pdf');
    }
}