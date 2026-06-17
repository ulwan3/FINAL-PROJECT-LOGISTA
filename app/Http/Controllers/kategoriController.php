<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    // Menampilkan daftar kategori
    public function index()
    {
        $kategoris = Kategori::withCount('barangs')->get();
        return view('kategori.index', compact('kategoris'));
    }

    // Fungsi menghapus kategori
    public function destroy($id)
    {
        // Gunakan find biasa agar tidak melempar exception kasar jika data double click
        $kategori = Kategori::find($id);

        if ($kategori) {
            // 1. Matikan pengecekan foreign key secara global (sementara)
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 2. Eksekusi hapus data kategori
            $kategori->delete();

            // 3. Hidupkan kembali pengecekan foreign key
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 4. FIX UTAMA: Jangan pakai route(), tapi langsung return back() agar halaman otomatis me-refresh aman
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}