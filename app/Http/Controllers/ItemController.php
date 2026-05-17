<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

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
            switch ($request->status) {
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
        switch ($request->sort ?? 'terbaru') {
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

        return view('items.index', compact(
            'barangs', 'kategoris', 'totalBarang',
            'countAman', 'countKritis', 'countKosong'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|unique:barangs',
            'nama_barang' => 'required',
            'kategori_id' => 'required',
            'satuan' => 'required',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
        ]);

        Barang::create($request->all());
        return back()->with('success', 'Barang berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $request->validate([
            'nama_barang' => 'required',
            'kategori_id' => 'required',
            'satuan' => 'required',
            'stok_minimum' => 'required|integer|min:0',
        ]);

        $barang->update($request->except(['kode_barang', 'stok'])); // Kode dan stok tak bisa diedit langsung dari form master
        return back()->with('success', 'Barang berhasil diubah');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        // Bisa tambahkan logic pengecekan apakah barang punya transaksi
        $barang->delete();
        return back()->with('success', 'Barang berhasil dihapus');
    }
}
