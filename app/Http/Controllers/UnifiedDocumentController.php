<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Region;
use App\Models\SupplierRafaksi;
use App\Models\Toko;
use App\Services\RafaksiExcelImportParser;
use Illuminate\Http\Request;

class UnifiedDocumentController extends Controller
{
    public function create()
    {
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::whereNotIn('status', ['nonaktif'])->get();
        $categories = Category::all();
        $ptOptions = Toko::whereNotNull('nama_pt')->where('nama_pt', '!=', '')->distinct()->orderBy('nama_pt')->pluck('nama_pt');

        return view('unified.create', compact('supplierRafaksi', 'regions', 'categories', 'ptOptions'));
    }

    /**
     * Parse-only: baca file Excel yang diupload, tidak menyimpan apapun ke DB.
     * Hasilnya dipakai frontend buat nampilin preview sebelum user approve.
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $parser = new RafaksiExcelImportParser();
        $result = $parser->parse($request->file('excel_file')->getRealPath());

        if (! empty($result['errors'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
