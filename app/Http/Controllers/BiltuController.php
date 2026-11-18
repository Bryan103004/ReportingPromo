<?php

namespace App\Http\Controllers;

use App\Models\Biltu;
use App\Models\ImportBiltuLog;
use App\Models\SupplierRafaksi;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BiltuController extends Controller
{
    /**
     * Menampilkan daftar Master Biltu dengan fitur pencarian dan filter
     */
    public function index(Request $request)
    {
        $query = Biltu::with('supplier')->orderBy('id', 'desc');

        // Filter keyword pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('C_BPartner_ID', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('vendor_no', 'like', "%{$search}%")
                  ->orWhere('taxid', 'like', "%{$search}%")
                  ->orWhere('City', 'like', "%{$search}%");
            });
        }

        // Filter Tipe PT (is_mba)
        if ($request->filled('filter_is_mba') && $request->filter_is_mba !== 'all') {
            $query->where('is_mba', (bool) $request->filter_is_mba);
        }

        // Filter Supplier (vendor_no)
        if ($request->filled('filter_vendor_no')) {
            $query->where('vendor_no', $request->filter_vendor_no);
        }

        $biltus = $query->customPaginate(15);
        $biltus->appends($request->all());

        // Ambil daftar supplier untuk pilihan filter
        $suppliers = SupplierRafaksi::orderBy('nama_supplier')->get(['kode_supplier', 'nama_supplier']);

        return view('biltu.index', compact('biltus', 'suppliers'));
    }

    /**
     * Form tambah Master Biltu
     */
    public function create()
    {
        $suppliers = SupplierRafaksi::orderBy('nama_supplier')->get(['kode_supplier', 'nama_supplier']);
        return view('biltu.create', compact('suppliers'));
    }

    /**
     * Menyimpan data Master Biltu baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'C_BPartner_ID' => 'required|string|max:255',
            'value'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('biltus')->where(function ($query) use ($request) {
                    return $query->where('C_BPartner_ID', $request->C_BPartner_ID);
                }),
            ],
            'name'          => 'required|string|max:255',
            'name2'         => 'nullable|string|max:255',
            'client'        => 'nullable|string|max:255',
            'vendor_no'     => 'nullable|string|max:255',
            'is_mba'        => 'required|boolean',
            'taxid'         => 'nullable|string|max:255',
            'Address1'      => 'nullable|string|max:255',
            'Address2'      => 'nullable|string|max:255',
            'Address3'      => 'nullable|string|max:255',
            'Address4'      => 'nullable|string|max:255',
            'City'          => 'nullable|string|max:255',
            'regionName'    => 'nullable|string|max:255',
            'postal'        => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:255',
            'phone2'        => 'nullable|string|max:255',
            'NITKU'         => 'nullable|string|max:255',
        ], [
            'value.unique' => 'Kombinasi C_BPartner_ID dan Search Key (Value) sudah ada di database.',
        ]);

        $data = $request->all();
        // Otomatis tentukan client jika kosong
        if (empty($data['client'])) {
            $data['client'] = $request->boolean('is_mba') ? 'PT Mitra Belanja Anda' : 'GrandLucky';
        }

        $biltu = Biltu::create($data);

        ActivityLogger::logCreate(
            $biltu,
            $biltu->id,
            $request->only(['C_BPartner_ID', 'value', 'name', 'vendor_no', 'is_mba']),
            "Created Master Biltu #{$biltu->id}: {$biltu->C_BPartner_ID} - {$biltu->value} ({$biltu->name})"
        );

        return redirect()->route('biltu.index')->with('success', 'Data Master Biltu berhasil ditambahkan.');
    }

    /**
     * Form edit Master Biltu
     */
    public function edit(Biltu $biltu)
    {
        $suppliers = SupplierRafaksi::orderBy('nama_supplier')->get(['kode_supplier', 'nama_supplier']);
        return view('biltu.edit', compact('biltu', 'suppliers'));
    }

    /**
     * Update data Master Biltu
     */
    public function update(Request $request, Biltu $biltu)
    {
        $request->validate([
            'C_BPartner_ID' => 'required|string|max:255',
            'value'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('biltus')->where(function ($query) use ($request) {
                    return $query->where('C_BPartner_ID', $request->C_BPartner_ID);
                })->ignore($biltu->id),
            ],
            'name'          => 'required|string|max:255',
            'name2'         => 'nullable|string|max:255',
            'client'        => 'nullable|string|max:255',
            'vendor_no'     => 'nullable|string|max:255',
            'is_mba'        => 'required|boolean',
            'taxid'         => 'nullable|string|max:255',
            'Address1'      => 'nullable|string|max:255',
            'Address2'      => 'nullable|string|max:255',
            'Address3'      => 'nullable|string|max:255',
            'Address4'      => 'nullable|string|max:255',
            'City'          => 'nullable|string|max:255',
            'regionName'    => 'nullable|string|max:255',
            'postal'        => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:255',
            'phone2'        => 'nullable|string|max:255',
            'NITKU'         => 'nullable|string|max:255',
        ], [
            'value.unique' => 'Kombinasi C_BPartner_ID dan Search Key (Value) sudah ada di database.',
        ]);

        $data = $request->all();
        if (empty($data['client'])) {
            $data['client'] = $request->boolean('is_mba') ? 'PT Mitra Belanja Anda' : 'GrandLucky';
        }

        $biltu->update($data);

        ActivityLogger::logUpdate(
            $biltu,
            $biltu->id,
            $request->only(['C_BPartner_ID', 'value', 'name', 'vendor_no', 'is_mba']),
            "Updated Master Biltu #{$biltu->id}: {$biltu->C_BPartner_ID} - {$biltu->value} ({$biltu->name})"
        );

        return redirect()->route('biltu.index')->with('success', 'Data Master Biltu berhasil diperbarui.');
    }

    /**
     * Menghapus data Master Biltu
     */
    public function destroy(Biltu $biltu)
    {
        $info = "{$biltu->C_BPartner_ID} - {$biltu->value} ({$biltu->name})";
        $id = $biltu->id;
        $biltu->delete();

        ActivityLogger::logDelete(
            $biltu,
            $id,
            ['C_BPartner_ID' => $biltu->C_BPartner_ID, 'value' => $biltu->value, 'name' => $biltu->name],
            "Deleted Master Biltu #{$id}: {$info}"
        );

        return redirect()->route('biltu.index')->with('success', 'Data Master Biltu berhasil dihapus.');
    }

    /**
     * Menampilkan riwayat log penarikan/import data Biltu dari folder inbound
     */
    public function importLogs(Request $request)
    {
        $logs = ImportBiltuLog::orderBy('started_at', 'desc')->paginate(20);
        return view('biltu.import_logs', compact('logs'));
    }
}
