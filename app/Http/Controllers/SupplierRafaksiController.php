<?php

namespace App\Http\Controllers;

use App\Models\SupplierRafaksi;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SupplierRafaksiController extends Controller
{
    //
    public function index(){
        $supplier_rafaksi = SupplierRafaksi::customPaginate(); 
        return view('supplier_rafaksi.index', compact('supplier_rafaksi'));
    }

    public function create(){
        return view('supplier_rafaksi.create');
    }

    public function store(Request $request){
        $request->validate([
            'kode_supplier' => 'string|required',
            'nama_supplier' => 'string|required',
        ]);

        $rafaksi = SupplierRafaksi::create($request->all());

        ActivityLogger::logCreate(
            $rafaksi,
            $rafaksi->id,
            $request->only(['kode_supplier', 'nama_supplier']),
            "Created Master Supplier Rafaksi #{$rafaksi->id}: {$rafaksi->nama_supplier}"
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data Supplier berhasil ditambahkan.',
                'data' => $rafaksi // Mengirim balik data jika diperlukan oleh frontend
            ]);
        }

        return redirect()->route('supplier_rafaksi.index')->with('success', 'Data Supplier Rafaksi berhasil disimpan.');
    }

    public function edit(SupplierRafaksi $supplier_rafaksi){
        return view('supplier_rafaksi.edit', compact('supplier_rafaksi'));
    }

    public function update(Request $request, SupplierRafaksi $supplier_rafaksi){
        $request->validate([
            'kode_supplier' => 'string|required',
            'nama_supplier' => 'string|required',
        ]);

        $supplier_rafaksi->update($request->all());

        ActivityLogger::logUpdate(
            $supplier_rafaksi,
            $supplier_rafaksi->id,
            $request->only(['kode_supplier', 'nama_supplier']),
            "Updated Master Supplier Rafaksi #{$supplier_rafaksi->id}: {$supplier_rafaksi->nama_supplier}"
        );

        return redirect()->route('supplier_rafaksi.index')->with('success', 'Data Supplier Rafaksi berhasil diperbarui.');
    }

    public function destroy(SupplierRafaksi $supplier_rafaksi){
        $supplier_rafaksi->delete();

        ActivityLogger::logDelete(
            $supplier_rafaksi,
            $supplier_rafaksi->id,
            ['kode_supplier' => $supplier_rafaksi->kode_supplier, 'nama_supplier' => $supplier_rafaksi->nama_supplier],
            "Deleted Master Supplier Rafaksi #{$supplier_rafaksi->id}: {$supplier_rafaksi->nama_supplier}"
        );

        return redirect()->route('supplier_rafaksi.index')->with('success', 'Data Supplier Rafaksi berhasil dihapus.');
    }
}
