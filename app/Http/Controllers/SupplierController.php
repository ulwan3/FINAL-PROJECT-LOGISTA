<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->paginate(10);
        return view('supplier.index', compact('suppliers'));
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        Supplier::create([
            'nama_supplier' => $request->nama_supplier,
            'kontak' => $request->kontak,
            'alamat' => $request->alamat,
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil ditambahkan!');
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
        ]);

        $supplier->update([
            'nama_supplier' => $request->nama_supplier,
            'kontak' => $request->kontak,
            'alamat' => $request->alamat,
        ]);

        return redirect()->back()->with('success', 'Supplier berhasil diupdate!');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier berhasil dihapus!');
    }
}