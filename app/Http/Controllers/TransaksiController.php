<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PemesananBarang; // Memastikan pakai model PemesananBarang
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        // 2. Simpan ke tabel pemesanan_barangs (stok tidak langsung berubah)
        PemesananBarang::create([
            'barang_id' => $request->barang_id,
            'supplier_id' => $request->supplier_id,
            'user_id'   => Auth::id() ?? 1, // ID Admin
            'jumlah_pesan'    => $request->jumlah,
            'status'    => 'pending', // Menunggu operator mobile
            'tanggal_pesan' => now(),
        ]);

        // 3. Kembali dengan notifikasi
        return redirect()->back()->with('success', 'Pesanan ke supplier berhasil dibuat!');
    }

    public function history()
{
    // Mengambil semua data pesanan, urutkan dari yang terbaru
    $allOrders = \App\Models\PemesananBarang::with(['barang', 'user', 'supplier'])
                ->orderBy('created_at', 'desc')
                ->paginate(10); // Pakai paginate supaya kalau data ribuan tidak berat

    return view('pesanan.history', compact('allOrders'));
}
}