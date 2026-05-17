<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        $transaksis = Transaksi::with(['barang', 'user'])->orderBy('created_at', 'desc')->paginate(15);
        $totalLogs = Transaksi::count();
        return view('audit.index', compact('transaksis', 'totalLogs'));
    }
}
