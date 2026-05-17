<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan nama tabel SESUAI database: pemesanan_barang (tanpa s)
        Schema::table('pemesanan_barang', function (Blueprint $table) {
            // 1. Tambahkan user_id (Wajib agar tahu siapa yang pesan)
            if (!Schema::hasColumn('pemesanan_barang', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users');
            }

            // 2. Tambahkan status
            if (!Schema::hasColumn('pemesanan_barang', 'status')) {
                $table->enum('status', ['pending', 'diproses', 'sampai', 'tidak_sesuai'])
                      ->default('pending')
                      ->after('jumlah_pesan'); // Sesuaikan posisi kolomnya
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_barang', function (Blueprint $table) {
            $table->dropColumn(['status', 'user_id']);
        });
    }
};