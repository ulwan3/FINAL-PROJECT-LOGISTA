<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    public $timestamps = false;

    protected $fillable = [
        'pemesanan_id', 'barang_id', 'jumlah_masuk', 'diterima_oleh', 'tanggal_masuk'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function pemesanan()
    {
        return $this->belongsTo(PemesananBarang::class, 'pemesanan_id');
    }
}
