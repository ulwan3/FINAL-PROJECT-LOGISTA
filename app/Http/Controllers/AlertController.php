<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $kritis = Barang::where('stok', 0)->get();
        $menipis = Barang::whereColumn('stok', '<=', 'stok_minimum')->where('stok', '>', 0)->get();

        return view('alerts.index', compact('kritis', 'menipis'));
    }
}
