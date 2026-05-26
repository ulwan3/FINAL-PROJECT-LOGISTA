<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// WAJIB TAMBAHKAN BARIS INI:
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemesananBarang extends Model
{
    protected $table = 'pemesanan_barang';
    
    // Jika tabel kamu tidak punya kolom updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'barang_id', 
        'supplier_id',  // ID Supplier yang dipilih
        'nama_supplier',
        'user_id',      // Pastikan user_id bisa diisi
        'jumlah_pesan', // Sesuaikan dengan nama di database kamu
        'status', 
        'qty_diterima', 
        'catatan', 
        'verified_at'
    ];

    /**
     * Relasi ke Barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi ke User (Admin yang memesan)
     */
    public function user(): BelongsTo
    {
        // Jika kolom di database namanya user_id, ini sudah benar
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplier()
{
    return $this->belongsTo(Supplier::class, 'supplier_id');
}
}