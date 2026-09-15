<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Rafaksi;
use App\Models\Jsm;
use App\Models\Pwp;
use Carbon\Carbon;

class UtilityController extends Controller
{
    public function nextNoRaf(Request $request)
    {
        $category = strtoupper($request->query('category', 'RAF'));
        $cat = Category::find($request->query('category_id'));
        $cat_init = $cat ? $cat->initial_category : '';
        $categoryId = $cat ? $cat->id : $request->query('category_id');
        $periode = $request->query('periode');
        $date = $periode ? Carbon::parse($periode) : Carbon::now();
        $month = $date->format('m');
        $year = $date->format('Y');

        if ($category === 'JSM') {
            $maxSeq = Jsm::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAFJSM';
        } elseif ($category === 'PWP') {
            $maxSeq = Pwp::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAFPWP';
        } else {
            $maxSeq = Rafaksi::where('no_raf', 'like', "%/{$year}")->where('category_id', $categoryId)->max('raf_sequence');
            $prefix = 'RAF';
        }

        $nextSeq = $maxSeq ? $maxSeq + 1 : 1;
        $padded = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'no_raf' => "{$prefix}/{$cat_init}/{$padded}/{$month}/{$year}",
            'sequence' => $nextSeq,
        ]);
    }

    public function selectStoresForPrint($type, $id)
    {   
        $type = strtolower($type);
        $relations = ['tokos', 'items.stores'];

        switch ($type) {
            case 'jsm':
                $document = Jsm::with($relations)->findOrFail($id);
                $users = User::where('is_jsm_spv', true)->get();
                break;
            case 'pwp':
                $document = Pwp::with($relations)->findOrFail($id);
                $users = User::where('is_pwp_spv', true)->get();
                break;
            default:
                $document = Rafaksi::with($relations)->findOrFail($id);
                $users = User::where('is_rafaksi_spv', true)->get();
                break;
        }

        // Kalau dokumen punya baris item, checklist toko cetak cuma nampilin toko
        // yang beneran punya sales (>0) di salah satu item -- toko yang cuma
        // "numpang" ke-link tapi datanya kosong semua (selalu jadi '-' di hasil
        // cetak) gak usah dikasih pilihan, biar gak bikin bingung. Dokumen lama
        // yang belum punya baris item (manual/flat) tetap tampilkan semua toko
        // yang di-link, karena filter per-item ini gak berlaku buat dokumen itu.
        if ($document->items->isNotEmpty()) {
            $tokoIdsWithData = $document->items
                ->flatMap(fn ($item) => $item->stores)
                ->where('sales_qty', '>', 0)
                ->pluck('toko_id')
                ->unique();

            $document->setRelation('tokos', $document->tokos->whereIn('id', $tokoIdsWithData));
        }

        $title = 'Pilih Toko Cetak';
        return view('layouts.print-filter', compact('document', 'title', 'type', 'users'));
    }

    public function printDocument(Request $request, $type, $id)
    {
        $type = strtolower($type);

        // $prepared_by = User::find($request->input('prepared_by'));
        $prepared_by = auth()->user(); // Gunakan user yang sedang login sebagai prepared_by
        $acknowledged_by = User::find($request->input('acknowledged_by'));

        if($prepared_by == null || $acknowledged_by == null){
            return redirect()->back()->with('error', 'Prepared By atau Acknowledged By tidak ditemukan. Pastikan Tipe Dokumen ini memilki SPV yang sesuai.');
        }

        $prepared_by = $prepared_by->name;
        $acknowledged_by = $acknowledged_by->name;

        // Ambil input array toko_ids yang dicentang dari form filter
        $selectedTokoIds = $request->input('toko_ids', []);

        // Load relasi standar
        $relations = ['items.stores.toko', 'tokos'];

        switch ($type) {
            case 'jsm':
                $document = Jsm::with($relations)->findOrFail($id);
                $title = 'DOKUMEN JSM';
                $prepared_by;
                $acknowledged_by;
                break;
            case 'pwp':
                $document = Pwp::with($relations)->findOrFail($id);
                $title = 'DOKUMEN PWP';
                $prepared_by;
                $acknowledged_by;
                break;
            default:
                $document = Rafaksi::with($relations)->findOrFail($id);
                $title = 'DOKUMEN RAFRAKSI';
                $prepared_by;
                $acknowledged_by;
                break;
        }

        // Filter daftar toko utama dokumen agar hanya menampilkan yang dipilih user
        if (!empty($selectedTokoIds)) {
            $document->setRelation('tokos', $document->tokos->whereIn('id', $selectedTokoIds));
            
            // Filter juga rincian stores di dalam setiap item agar hanya menghitung toko yang dipilih
            foreach ($document->items as $item) {
                $filteredStores = $item->stores->whereIn('toko_id', $selectedTokoIds);
                $item->setRelation('stores', $filteredStores);

                // HITUNG ULANG TOTAL SECARA DINAMIS BERDASARKAN TOKO YANG DIPILIH
                $item->sales_total = $filteredStores->sum('sales_qty');
                $item->value_total = $filteredStores->sum('value');
            }
        }


        return view('layouts.print-universal', compact('document', 'title', 'type', 'prepared_by', 'acknowledged_by'));
    }
}
