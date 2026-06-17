<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Services\StokAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    private function totalStokSql()
    {
        return '(COALESCE(barangs.stok_gudang, 0) + COALESCE(barangs.stok_rak, 0))';
    }

    public function index(Request $request)
    {
        $totalStokSql = $this->totalStokSql();

        $query = Barang::with('kategori')
            ->select('barangs.*')
            ->selectRaw("$totalStokSql as total_stok_hitung");

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->filled('status')) {
            $switchStatus = $request->status;
            switch ($switchStatus) {
                case 'aman':
                    $query->whereRaw("$totalStokSql > barangs.stok_minimum");
                    break;

                case 'kritis':
                    $query->whereRaw("$totalStokSql <= barangs.stok_minimum")
                        ->whereRaw("$totalStokSql > 0");
                    break;

                case 'kosong':
                    $query->whereRaw("$totalStokSql <= 0");
                    break;
            }
        }

        $sortValue = $request->sort ?? 'terbaru';

        switch ($sortValue) {
            case 'terlama':
                $query->orderBy('id', 'asc');
                break;

            case 'nama_asc':
                $query->orderBy('nama_barang', 'asc');
                break;

            case 'nama_desc':
                $query->orderBy('nama_barang', 'desc');
                break;

            case 'stok_asc':
                $query->orderByRaw("$totalStokSql ASC");
                break;

            case 'stok_desc':
                $query->orderByRaw("$totalStokSql DESC");
                break;

            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $barangs = $query->paginate(10)->withQueryString();
        $kategoris = Kategori::all();
        $totalBarang = Barang::count();

        $countAman = Barang::whereRaw("$totalStokSql > barangs.stok_minimum")
            ->count();

        $countKritis = Barang::whereRaw("$totalStokSql <= barangs.stok_minimum")
            ->whereRaw("$totalStokSql > 0")
            ->count();

        $countKosong = Barang::whereRaw("$totalStokSql <= 0")
            ->count();

        $latestBarang = Barang::latest('id')->first();
        $nextNumber = 1;

        if ($latestBarang && $latestBarang->kode_barang) {
            $numericPart = preg_replace('/[^0-9]/', '', $latestBarang->kode_barang);

            if (!empty($numericPart)) {
                $nextNumber = (int) $numericPart + 1;
            }
        }

        $nextKodeBarang = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('items.index', compact(
            'barangs',
            'kategoris',
            'totalBarang',
            'countAman',
            'countKritis',
            'countKosong',
            'nextKodeBarang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'    => 'required',
            'kategori_input' => 'required',
            'satuan'         => 'required',
            'stok'           => 'required|integer|min:0',
            'stok_minimum'   => 'required|integer|min:0',
        ]);

        $stokAwal = (int) $request->stok;

        $latestBarang = Barang::latest('id')->first();
        $nextNumber = 1;

        if ($latestBarang && $latestBarang->kode_barang) {
            $numericPart = preg_replace('/[^0-9]/', '', $latestBarang->kode_barang);

            if (!empty($numericPart)) {
                $nextNumber = (int) $numericPart + 1;
            }
        }

        $autoKodeBarang = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $namaKategori = strtoupper(trim($request->kategori_input));
        $slugKategori = Str::slug($namaKategori);

        $kategori = Kategori::firstOrCreate(
            ['nama' => $namaKategori],
            ['slug' => $slugKategori]
        );

        $barangBaru = new Barang();
        $barangBaru->kode_barang = $autoKodeBarang;
        $barangBaru->nama_barang = $request->nama_barang;
        $barangBaru->kategori_id = $kategori->id;
        $barangBaru->satuan = $request->satuan;
        $barangBaru->stok_minimum = (int) $request->stok_minimum;
        $barangBaru->stok_gudang = $stokAwal;
        $barangBaru->stok_rak = 0;
        $barangBaru->stok = $stokAwal;
        $barangBaru->total_stok = $stokAwal;
        $barangBaru->save();

        $kodeTrxOtomatis = 'TRX-MASUK-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // 🔥 FIX UTAMA: Menyesuaikan nama key array dengan nama asli kolom database kamu
        Transaksi::create([
            'kode_transaksi'      => $kodeTrxOtomatis,
            'jenis'               => 'barang_masuk',
            'barang_id'           => $barangBaru->id,
            'jumlah'              => $stokAwal,
            'total_stok_sebelum'  => 0,            // Mengganti 'stok_sebelum'
            'total_stok_sesudah'  => $stokAwal,     // Mengganti 'stok_sesudah'
            'stok_gudang_sebelum' => 0,            // Ditambahkan agar tidak NULL
            'stok_gudang_sesudah' => $stokAwal,     // Ditambahkan agar tidak NULL
            'stok_rak_sebelum'    => 0,            // Ditambahkan agar tidak NULL
            'stok_rak_sesudah'    => 0,            // Ditambahkan agar tidak NULL
            'keterangan'          => 'Stok Awal Barang Baru (' . $barangBaru->kode_barang . ')',
            'user_id'             => auth()->id() ?? 2,
        ]);

        StokAlertService::cekDanKirim($barangBaru);

        return redirect('/items')->with(
            'success',
            'Barang berhasil ditambahkan dengan Kode: ' . $autoKodeBarang
        );
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $request->validate([
            'nama_barang'    => 'required',
            'kategori_input' => 'required',
            'satuan'         => 'required',
            'stok_minimum'   => 'required|integer|min:0',
        ]);

        $namaKategori = strtoupper(trim($request->kategori_input));
        $slugKategori = Str::slug($namaKategori);

        $kategori = Kategori::firstOrCreate(
            ['nama' => $namaKategori],
            ['slug' => $slugKategori]
        );

        $barang->nama_barang = $request->nama_barang;
        $barang->kategori_id = $kategori->id;
        $barang->satuan = $request->satuan;
        $barang->stok_minimum = (int) $request->stok_minimum;

        $totalStok = ($barang->stok_gudang ?? 0) + ($barang->stok_rak ?? 0);

        $barang->stok = $totalStok;
        $barang->total_stok = $totalStok;

        $barang->save();

        StokAlertService::cekDanKirim($barang);

        return back()->with('success', 'Barang berhasil diubah');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return back()->with('success', 'Barang berhasil dihapus');
    }
}