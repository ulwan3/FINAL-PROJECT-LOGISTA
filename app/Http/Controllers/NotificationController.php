<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DB::table('notifications')
            ->select(
                'id',
                'title',
                'message',
                'type',
                'pemesanan_id',
                'is_read',
                'created_at'
            )
            ->where('is_read', 0)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function count()
    {
        $total = DB::table('notifications')
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success' => true,
            'total' => $total
        ]);
    }
}