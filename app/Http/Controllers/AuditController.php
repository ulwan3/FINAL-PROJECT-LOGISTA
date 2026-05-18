<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\User; // 1. PASTIKAN LINE INI ADA DI ATAS
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['barang', 'user'])->latest();

        // Filter Waktu Tunggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // Filter Modul / Jenis Transaksi
        if ($request->filled('modul') && $request->modul !== 'Semua Aktivitas') {
            $query->where('jenis', $request->modul);
        }

        // Filter Operator
        if ($request->filled('operator') && $request->operator !== 'Semua Operator') {
            $query->where('user_id', $request->operator);
        }

        $transaksis = $query->paginate(15);
        
        // 2. AMBIL DATA USER UNTUK DROPDOWN OPERATOR
        $users = User::all(); 

        // 3. PASTIKAN '$users' IKUT DI MASUKKAN KE COMPACT DI SINI
        return view('audit.index', compact('transaksis', 'users'));
    }
}