<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Karena sistem login belum terpasang sepenuhnya, kita ambil user pertama sebagai contoh
        $user = User::first();
        
        return view('settings.index', compact('user'));
    }
}
