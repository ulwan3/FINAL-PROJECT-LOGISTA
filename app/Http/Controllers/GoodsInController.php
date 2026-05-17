<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PemesananBarang;
use Illuminate\Http\Request;

class GoodsInController extends Controller
{
    public function index()
    {
        $barangs = Barang::all();
        $poList = PemesananBarang::with('barang')->orderBy('created_at', 'desc')->take(10)->get();
        
        return view('ops.goods-in', compact('barangs', 'poList'));
    }
}
