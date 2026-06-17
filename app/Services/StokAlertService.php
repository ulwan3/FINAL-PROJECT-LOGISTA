<?php

namespace App\Services;

use App\Mail\StokAlertMail;
use App\Models\Barang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StokAlertService
{
    public static function cekDanKirim(Barang $barang): void
    {
        // Refresh data barang dari database supaya stok terbaru kebaca
        $barang->refresh();

        $stokGudang = (int) ($barang->stok_gudang ?? 0);
        $stokRak = (int) ($barang->stok_rak ?? 0);
        $stokMinimum = (int) ($barang->stok_minimum ?? 0);

        // Rumus stok sesuai Master Item
        $stokSaatIni = $stokGudang + $stokRak;

        // Tentukan status stok saat ini
        if ($stokSaatIni <= 0) {
            $statusSekarang = 'habis';
        } elseif ($stokSaatIni <= $stokMinimum) {
            $statusSekarang = 'menipis';
        } else {
            $statusSekarang = 'aman';
        }

        // Kalau status aman, reset status alert supaya nanti kalau turun lagi bisa kirim email lagi
        if ($statusSekarang === 'aman') {
            if ($barang->last_alert_status !== 'aman') {
                $barang->last_alert_status = 'aman';
                $barang->last_alert_sent_at = null;
                $barang->save();
            }

            return;
        }

        // Anti spam:
        // Kalau status sekarang sama dengan status terakhir, jangan kirim email lagi
        if ($barang->last_alert_status === $statusSekarang) {
            return;
        }

        $emailTujuan = config('logista.alert_email');

        if (!$emailTujuan) {
            Log::warning('Email tujuan stok alert belum disetting di .env LOGISTA_ALERT_EMAIL');

            return;
        }

        try {
            Mail::to($emailTujuan)->send(
                new StokAlertMail($barang, $statusSekarang, $stokSaatIni)
            );

            // Simpan status terakhir setelah email berhasil dikirim
            $barang->last_alert_status = $statusSekarang;
            $barang->last_alert_sent_at = now();
            $barang->save();

        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email stok alert', [
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'status' => $statusSekarang,
                'error' => $e->getMessage(),
            ]);
        }
    }
}