<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class kategoriController extends Controller
{
    // Menampilkan daftar kategori (bisa kamu pakai untuk halaman baru nanti)
    public function index()
    {
        $kategoris = Kategori::withCount('barangs')->get();
        return view('kategori.index', compact('kategoris'));
    }

    // Fungsi menghapus kategori
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Proteksi: Cek apakah kategori ini sedang dipakai oleh barang
        if ($kategori->barangs()->count() > 0) {
            return back()->with('error', 'Kategori "' . $kategori->nama . '" tidak bisa dihapus karena masih ada barang di dalamnya!');
        }

        $kategori->delete();
        return redirect()->route('items.index')->with('success', 'Kategori berhasil dihapus!');
    }
}