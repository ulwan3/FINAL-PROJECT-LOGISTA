<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $masuk = Transaksi::where('jenis', 'masuk')->sum('jumlah');
        $keluar = Transaksi::where('jenis', 'keluar')->sum('jumlah');
        return view('reports.index', compact('masuk', 'keluar'));
    }
}
