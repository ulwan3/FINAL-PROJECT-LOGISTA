<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(): View
    {
        // Ambil operator yang belum dihapus (soft delete)
        $operators = User::where('role', 'operator')
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc')
            ->get();
        
        // Tentukan status online/offline
        foreach ($operators as $operator) {
            $operator->is_online = false;
            if ($operator->last_seen_at) {
                $lastSeen = Carbon::parse($operator->last_seen_at);
                $operator->is_online = $lastSeen->diffInSeconds(now()) <= 5;
            }
        }
        
        // =====================================
        // NOTIFIKASI STOK
        // =====================================
        
        // Ambil data stok habis (stok <= 0)
        $stokKritisNotif = Barang::where('stok', '<=', 0)
            ->orWhere('total_stok', '<=', 0)
            ->get();
        
        // Ambil data stok menipis (stok <= stok_minimum AND stok > 0)
        $stokMenipisNotif = Barang::whereRaw('stok <= stok_minimum AND stok > 0')
            ->orWhereRaw('total_stok <= stok_minimum AND total_stok > 0')
            ->get();
        
        // Hitung total notifikasi
        $jumlahNotifStok = $stokKritisNotif->count() + $stokMenipisNotif->count();
        
        return view('settings.index', compact(
            'operators', 
            'stokKritisNotif', 
            'stokMenipisNotif', 
            'jumlahNotifStok'
        ));
    }
    
    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);
        
        User::where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
    
    /**
     * Toggle operator active status.
     */
    public function toggleOperatorActive(int $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->whereNull('deleted_at')->findOrFail($id);
        
        $newStatus = !$operator->is_active;
        $operator->update(['is_active' => $newStatus]);
        
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => 'Operator "' . $operator->name . '" berhasil ' . $statusText . '.'
        ]);
    }
    
    /**
     * Delete (soft delete) an operator.
     */
    public function deleteOperator(int $id): JsonResponse
    {
        $operator = User::where('role', 'operator')->whereNull('deleted_at')->findOrFail($id);
        $operatorName = $operator->name;
        
        $operator->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Operator "' . $operatorName . '" berhasil dihapus.'
        ]);
    }
    
    /**
     * Deactivate all operators.
     */
    public function deactivateAllOperators(): JsonResponse
    {
        User::where('role', 'operator')->whereNull('deleted_at')->update([
            'is_active' => false
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Semua operator berhasil dinonaktifkan aksesnya.'
        ]);
    }
}