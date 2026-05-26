<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PemesananBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\FirebasePushService;
use Illuminate\Support\Facades\Log;

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

        // 2. Ambil data barang untuk isi pesan notifikasi
        $barang = Barang::findOrFail($request->barang_id);

        // 3. Ambil nama supplier jika ada
        $supplier = null;

        if ($request->supplier_id) {
            $supplier = DB::table('suppliers')
                ->where('id', $request->supplier_id)
                ->first();
        }

        // 4. Simpan pemesanan dan notifikasi dalam transaction
       $notificationData = DB::transaction(function () use ($request, $barang, $supplier) {

    $pemesanan = PemesananBarang::create([
        'barang_id' => $request->barang_id,
        'supplier_id' => $request->supplier_id,
        'user_id' => Auth::id() ?? 1,
        'jumlah_pesan' => $request->jumlah,
        'status' => 'pending',
        'tanggal_pesan' => now(),
    ]);

    $namaSupplier = $supplier
        ? $supplier->nama_supplier
        : 'supplier tidak diketahui';

    $notifTitle = '📦 Pemesanan Baru';

    $notifMessage =
        $barang->nama_barang .
        ' dipesan sebanyak ' .
        $request->jumlah .
        ' dari ' .
        $namaSupplier;

    DB::table('notifications')->insert([
        'title' => $notifTitle,
        'message' => $notifMessage,
        'type' => 'pemesanan',
        'pemesanan_id' => $pemesanan->id,
        'is_read' => 0,
        'created_at' => now(),
    ]);

    return [
        'title' => $notifTitle,
        'message' => $notifMessage,
        'pemesanan_id' => $pemesanan->id,
        'barang_id' => $request->barang_id,
    ];
});
           // 5. Kirim push notification ke HP user
        try {
            app(FirebasePushService::class)->sendToAllTokens(
                $notificationData['title'],
                $notificationData['message'],
                [
                    'type' => 'pemesanan',
                    'pemesanan_id' => $notificationData['pemesanan_id'],
                    'barang_id' => $notificationData['barang_id'],
                    'page' => 'barang-masuk',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Gagal mengirim push notification.', [
                'message' => $e->getMessage(),
            ]);
        }


        // 6. Kembali dengan notifikasi Laravel
        return redirect()->back()->with(
            'success',
            'Pesanan ke supplier berhasil dibuat!'
        );
    }

    public function history()
    {
        // Mengambil semua data pesanan, urutkan dari yang terbaru
        $allOrders = PemesananBarang::with(['barang', 'user', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pesanan.history', compact('allOrders'));
    }
}