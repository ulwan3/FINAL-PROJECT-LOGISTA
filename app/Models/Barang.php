<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = [
        'kode_barang', 'nama_barang', 'kategori_id', 'stok', 'stok_minimum', 
        'satuan', 'harga_beli', 'harga_jual', 'lokasi_rak', 'deskripsi'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function getStatusStokAttribute()
    {
    if ($this->stok <= 0) {
        return 'Habis';
    } elseif ($this->stok <= $this->stok_minimum) {
        return 'Menipis';
    } else {
        return 'Aman';
    }
    }

}
