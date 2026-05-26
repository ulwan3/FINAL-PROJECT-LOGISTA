<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user_id' => 'nullable|integer',
            'platform' => 'nullable|string|max:50'
        ]);

        $existingToken = DB::table('fcm_tokens')
            ->where('token', $request->token)
            ->first();

        if ($existingToken) {
            DB::table('fcm_tokens')
                ->where('token', $request->token)
                ->update([
                    'user_id' => $request->user_id,
                    'platform' => $request->platform,
                    'updated_at' => now()
                ]);
        } else {
            DB::table('fcm_tokens')->insert([
                'user_id' => $request->user_id,
                'token' => $request->token,
                'platform' => $request->platform,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'FCM token berhasil disimpan'
        ]);
    }
}