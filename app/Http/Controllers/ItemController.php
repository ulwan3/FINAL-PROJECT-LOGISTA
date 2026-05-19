<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with('kategori');

        // Search by nama_barang or kode_barang
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        // Filter by stock status
        if ($request->filled('status')) {
            $value = $request->status;
            switch ($value) {
                case 'aman':
                    $query->whereColumn('stok', '>', 'stok_minimum');
                    break;
                case 'kritis':
                    $query->whereColumn('stok', '<=', 'stok_minimum')
                          ->where('stok', '>', 0);
                    break;
                case 'kosong':
                    $query->where('stok', '<=', 0);
                    break;
            }
        }

        // Sort options
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
                $query->orderBy('stok', 'asc');
                break;
            case 'stok_desc':
                $query->orderBy('stok', 'desc');
                break;
            default: // terbaru
                $query->orderBy('id', 'desc');
                break;
        }

        $barangs = $query->paginate(10)->withQueryString();
        $kategoris = Kategori::all();
        $totalBarang = Barang::count();

        // Count per status for badges
        $countAman = Barang::whereColumn('stok', '>', 'stok_minimum')->count();
        $countKritis = Barang::whereColumn('stok', '<=', 'stok_minimum')->where('stok', '>', 0)->count();
        $countKosong = Barang::where('stok', '<=', 0)->count();

        // --- BERIKUT LOGIKA COPIED UNTUK MENGINTIP KODE BERIKUTNYA ---
        $latestBarang = Barang::latest('id')->first();
        $nextNumber = 1;

        if ($latestBarang && $latestBarang->kode_barang) {
            $numericPart = preg_replace('/[^0-9]/', '', $latestBarang->kode_barang);
            if (!empty($numericPart)) {
                $nextNumber = (int)$numericPart + 1;
            }
        }
        
        $nextKodeBarang = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        // -------------------------------------------------------------

        // Menambahkan 'nextKodeBarang' ke dalam list compact
        return view('items.index', compact(
            'barangs', 'kategoris', 'totalBarang',
            'countAman', 'countKritis', 'countKosong', 'nextKodeBarang'
        ));
    }

   public function store(Request $request)
    {
        // 1. Validasi input form (menangkap kategori_input dari elemen datalist/text)
        $request->validate([
            'nama_barang'    => 'required',
            'kategori_input' => 'required', 
            'satuan'         => 'required',
            'stok'           => 'required|integer|min:0',
            'stok_minimum'   => 'required|integer|min:0',
        ]);

        // 2. Logika Pembuatan Kode Barang Otomatis (Format: BRG-0034, dst)
        $latestBarang = Barang::latest('id')->first();
        $nextNumber = 1;

        if ($latestBarang && $latestBarang->kode_barang) {
            $numericPart = preg_replace('/[^0-9]/', '', $latestBarang->kode_barang);
            if (!empty($numericPart)) {
                $nextNumber = (int)$numericPart + 1;
            }
        }
        
        $autoKodeBarang = 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // 3. Logika Otomatisasi Kategori Baru / Lama
        // Kita ubah teksnya jadi UPPERCASE biar rapi dan konsisten di DB (misal: "baju" jadi "BAJU")
        $namaKategori = strtoupper(trim($request->kategori_input));
        $slugKategori = Str::slug($namaKategori);
        
        // Cek database. Kalau nama kategori sudah ada, ambil ID-nya. Kalau belum ada, otomatis buat baru.
        $kategori = Kategori::firstOrCreate(
            ['nama' => $namaKategori],
            ['slug' => $slugKategori]
        );

        // 4. Penggabungan Data untuk Disimpan
        $data = $request->except(['kategori_input']); // Buang teks mentah kategori_input agar tidak crash
        $data['kode_barang'] = $autoKodeBarang;       // Masukkan kode otomatis (BRG-XXXX)
        $data['kategori_id'] = $kategori->id;         // Masukkan ID kategori yang sah hasil check/create

        // 5. Eksekusi simpan ke database tabel barangs
        $barangBaru = Barang::create($data);

        // Generate kode transaksi otomatis untuk stok awal barang baru
        $kodeTrxOtomatis = 'TRX-MASUK-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        // Asumsi nama Model kamu adalah Transaksi (jika berbeda, sesuaikan nama modelnya)
        \App\Models\Transaksi::create([
            'kode_transaksi' => $kodeTrxOtomatis,
            'jenis'          => 'masuk',
            'barang_id'      => $barangBaru->id,              // ID barang baru yang otomatis sinkron
            'jumlah'         => $barangBaru->stok,            // Diambil dari input stok awal
            'stok_sebelum'    => 0,                            // Stok sebelum ada barang baru pasti 0
            'stok_sesudah'     => $barangBaru->stok,            // Stok sesudah = stok awal
            'keterangan'     => 'Stok Awal Barang Baru (' . $barangBaru->kode_barang . ')', 
            'user_id'        => auth()->id() ?? 2,            // ID user login, default ke 2 sesuai data di screenshot kamu
        ]);

        // 6. Redirect kembali ke halaman master barang dengan alert sukses
        return redirect('/items')->with('success', 'Barang berhasil ditambahkan dengan Kode: ' . $autoKodeBarang);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        
        // 1. Validasi diubah dari 'kategori_id' menjadi 'kategori_input'
        $request->validate([
            'nama_barang' => 'required',
            'kategori_input' => 'required', // Menerima teks kategori bebas
            'satuan' => 'required',
            'stok_minimum' => 'required|integer|min:0',
        ]);
        
        $namaKategori = strtoupper(trim($request->kategori_input));
        $slugKategori = \Illuminate\Support\Str::slug($namaKategori);

        // 2. Logika otomatisasi cek/buat kategori baru
        $namaKategori = trim($request->kategori_input);
        $kategori = Kategori::firstOrCreate(
            ['nama' => $namaKategori],
            ['slug' => $slugKategori]
        );

        // 3. Ambil data input kecuali kode_barang, stok, dan kategori_input mentah
        $data = $request->except(['kode_barang', 'stok', 'kategori_input']);
        
        // 4. Selipkan id kategori hasil deteksi/pembuatan otomatis tadi
        $data['kategori_id'] = $kategori->id;

        $barang->update($data); 
        
        return back()->with('success', 'Barang berhasil diubah');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();
        return back()->with('success', 'Barang berhasil dihapus');
    }
}