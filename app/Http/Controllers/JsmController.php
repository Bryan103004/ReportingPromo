<?php

namespace App\Http\Controllers;

use App\Exports\DetailJsmReport;
use App\Models\Category;
use App\Models\Jsm;
use App\Models\JsmDocument;
use App\Models\Region;
use App\Models\SupplierRafaksi;
use App\Models\Toko;
use App\Services\ActivityLogger;
use App\Services\ClaimCalculator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetailJmsReport;
use Barryvdh\DomPDF\Facade\Pdf;

class JsmController extends Controller
{
    //
    // public function index()
    // {
    //     $jsmGroups = Jsm::selectRaw('
    //             store,  
    //             MAX(YEAR(periode_akhir)) as year, -- Mengambil tahun terbaru dalam grup
    //             MAX(MONTH(periode_akhir)) as month, -- Mengambil bulan terbaru dalam grup
    //             YEAR(periode_bulan) as year_kerja, 
    //             MONTH(periode_bulan) as month_kerja, 
    //             COUNT(*) as total_data, 
    //             SUM(nominal) as total_nominal
    //         ')
    //         ->groupBy('store', 'year_kerja', 'month_kerja')
    //         ->orderBy('year_kerja', 'asc')
    //         ->orderBy('month_kerja', 'asc')
    //         ->customPaginate();

    //     return view('jsm.index', compact('jsmGroups'));
    // }



    // Pastikan kamu meng-inject (Request $request) di parameternya
    public function index(Request $request)
    {
        // 1. Ambil data supplier & category untuk dikirim ke komponen filter dropdown
        $suppliers = SupplierRafaksi::all();
        $categories = Category::all();

        // Daftar toko yang boleh dilihat user (dibatasi kalau bukan admin/superadmin)
        $tokoQuery = Toko::whereNotIn('status', ['nonaktif']);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowedTokoIds = auth()->user()->accessibleTokoIds()->toArray();
            $tokos = empty($allowedTokoIds) ? collect() : $tokoQuery->whereIn('id', $allowedTokoIds)->get(['id', 'nama_toko']);
        } else {
            $tokos = $tokoQuery->get(['id', 'nama_toko']);
        }

        // 2. Siapkan Query Dasar
        $query = Jsm::selectRaw(' 
                MAX(YEAR(periode_akhir)) as year,
                MAX(MONTH(periode_akhir)) as month,
                YEAR(periode_bulan) as year_kerja, 
                MONTH(periode_bulan) as month_kerja, 
                COUNT(*) as total_data, 
                SUM(nominal) as total_nominal
            ');

        // 3. Terapkan Filter Jika Ada
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', $request->supplier_code);
        }
        
        if ($request->filled('start_date')) {
            $query->where('periode_awal', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('periode_akhir', '<=', $request->end_date);
        }

        // If user has limited toko access, restrict query
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $ids = auth()->user()->accessibleTokoIds()->toArray();
            if (empty($ids)) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('tokos', function($q) use ($ids) {
                    $q->whereIn('tokos.id', $ids);
                });
            }
        }

        // 4. Eksekusi Query menggunakan fungsi SQL asli di dalam groupBy dan orderBy
        $jsmGroups = $query->groupByRaw('YEAR(periode_bulan), MONTH(periode_bulan)') 
                        ->orderByRaw('YEAR(periode_bulan) ASC, MONTH(periode_bulan) ASC')
                        ->customPaginate();

        // 5. Appends Request (SANGAT PENTING!)
        // Ini agar saat kamu pindah ke Halaman 2, filter tidak hilang/reset
        $jsmGroups->appends($request->all());

        return view('jsm.index', compact('jsmGroups', 'suppliers', 'tokos', 'categories'));
    }

    // public function showMonth($year, $month)
    // {
    //     $jsms = Jsm::with(['tokos'])
    //         ->whereYear('periode_bulan', $year)
    //         ->whereMonth('periode_bulan', $month)
    //         ->orderBy('periode_akhir', 'desc') // Urutkan dari tanggal terbaru di bulan tersebut
    //         ->customPaginate(); 

    //     $periodeTitle = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

    //     return view('jsm.show_month', compact('jsms', 'periodeTitle', 'year', 'month'));
    // }

    public function showMonth(Request $request, $year, $month)
    {
        // 1. Ambil data supplier & category untuk dikirim ke komponen filter dropdown
        $suppliers = SupplierRafaksi::all();
        $categories = Category::all();

        // Daftar toko yang boleh dilihat user (dibatasi kalau bukan admin/superadmin)
        $tokoQuery = Toko::whereNotIn('status', ['nonaktif']);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowedTokoIds = auth()->user()->accessibleTokoIds()->toArray();
            
            if (empty($allowedTokoIds)) {
                $tokos = collect();
                $pts = collect(); // Menjamin variabel $pts tetap ada meskipun kosong
            } else {
                $tokos = $tokoQuery->whereIn('id', $allowedTokoIds)->get(['id', 'nama_toko', 'nama_pt']);
                
                // Mengambil list PT unik langsung lewat database SQL
                $pts = Toko::whereNotIn('status', ['nonaktif'])
                        ->whereIn('id', $allowedTokoIds)
                        ->distinct()
                        ->pluck('nama_pt');
            }
        } else {
            $tokos = $tokoQuery->get(['id', 'nama_toko', 'nama_pt']);
            
            // Mengambil list PT unik untuk akses global
            $pts = Toko::whereNotIn('status', ['nonaktif'])
                    ->distinct()
                    ->pluck('nama_pt');
        }
        
        // 2. Siapkan Query Builder Dasar (JANGAN panggil customPaginate di sini)
        // Pakai range tanggal (bukan whereYear/whereMonth) supaya index periode_bulan kepakai
        $periodeStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $periodeEnd = (clone $periodeStart)->addMonth();

        $query = Jsm::with(['tokos'])
            ->where('periode_bulan', '>=', $periodeStart)
            ->where('periode_bulan', '<', $periodeEnd)
            ->orderBy('created_at', 'desc');

        $periodeTitle = $periodeStart->translatedFormat('F Y');

        // 3. Terapkan Filter Jika Ada
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', $request->supplier_code);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('toko_id')) {
            $query->whereHas('tokos', function($q) use ($request) {
                $q->where('tokos.id', $request->toko_id);
            });
        }

        if ($request->filled('filter_pt')) {
            $query->whereHas('tokos', function($q) use ($request) {
                $q->where('tokos.nama_pt', $request->filter_pt);
            });
        }

        if ($request->filled('start_date')) {
            $query->where('periode_awal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('periode_akhir', '<=', $request->end_date);
        }

        if ($request->filled('no_raf')) {
            $query->where('no_raf', $request->no_raf);
        }

        // If user has limited toko access, restrict query
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $ids = auth()->user()->accessibleTokoIds()->toArray();
            if (empty($ids)) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('tokos', function($q) use ($ids) {
                    $q->whereIn('tokos.id', $ids);
                });
            }
        }

        // 4. Eksekusi Query dengan memanggil Pagination di bagian akhir
        $jsms = $query->customPaginate();

        // 5. Appends Request (SANGAT PENTING!)
        // Ini agar saat kamu pindah ke Halaman 2, filter tidak hilang/reset
        $jsms->appends($request->all());

        return view('jsm.show_month', compact('jsms', 'periodeTitle', 'year', 'month', 'suppliers', 'tokos', 'categories', 'pts'));
    }

    public function create(){
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::whereNotIn('status',['nonaktif'])->get();
        $categories = Category::all();
        return view('jsm.create', compact('supplierRafaksi','regions','categories'));
    }

    public function store(Request $request){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required|after_or_equal:periode_awal',
            'no_raf' => 'string|required',
            'no_shiji' => 'nullable|string',
            'no_shiji_promotion' => 'nullable|string',
            'status_email' => 'aktif',
            'periode_bulan' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'required_without:items_json|nullable|numeric|min:0',
            'remarks' => 'string|nullable',
            'toko_id' => 'array|required',
            'toko_id.*' => 'exists:tokos,id',
            'document_file.*' => 'nullable|file|mimes:pdf|max:5120',
            'category_id' => 'exists:categories,id',
        ]);

        $items = $this->decodeAndValidateItems($request);
        $this->assertItemsDiscMutuallyExclusive($items);

        $category = Category::find($request->category_id);

        $cat_init = $category->initial_category;

        $data = $request->except(['toko_id', 'items_json', 'document_file']);

        // Respect explicit raf_sequence if frontend provided
        if ($request->filled('raf_sequence')) {
            $data['raf_sequence'] = (int) $request->input('raf_sequence');
        }

        // Auto-generate raf_sequence and no_raf when not provided
        if (empty($data['no_raf'])) {
            $periode = Carbon::parse($data['periode_bulan']);
            $month = $periode->format('m');
            $year = $periode->format('Y');

            $maxSeq = Jsm::where('no_raf', 'like', "%/{$year}")
                ->where('category_id', $request->category_id)
                ->max('raf_sequence');

            $nextSeq = $maxSeq ? $maxSeq + 1 : 1;
            $padded = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

            if (empty($data['raf_sequence'])) {
                $data['raf_sequence'] = $nextSeq;
            }
            $data['no_raf'] = "RAFJSM/{$cat_init}/{$padded}/{$month}/{$year}";
        }

        $tokoIds = $request->input('toko_id', []);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowed = auth()->user()->accessibleTokoIds()->toArray();
            $tokoIds = array_values(array_intersect($tokoIds, $allowed));
        }

        $jsm = DB::transaction(function () use ($data, $tokoIds, $request, $items) {
            $jsm = Jsm::create($data);
            $jsm->tokos()->sync($tokoIds);

            $nominal = $this->persistItems($jsm, $items, $tokoIds);
            if ($nominal !== null) {
                $jsm->update(['nominal' => $nominal]);
            }

            $this->storeDocuments($jsm, $request);

            return $jsm;
        });

        ActivityLogger::logCreate(
            $jsm,
            $jsm->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Created Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal_formatted}"
        );

        return redirect()->route('jsm.index')->with('success', 'Data JSM berhasil disimpan.');
    }

    /**
     * Simpan file dokumen PDF yang di-upload (nambah, bukan mengganti/menghapus
     * dokumen yang sudah ada -- hapus dokumen dilakukan lewat tombol hapus
     * per-file di halaman edit, bukan otomatis pas submit form).
     */
    private function storeDocuments(Jsm $jsm, Request $request): void
    {
        if (! $request->hasFile('document_file')) {
            return;
        }

        $dir = 'jsm_documents/' . $jsm->id;

        foreach ($request->file('document_file') as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = $originalName;
            if (Storage::exists($dir . '/' . $storedName)) {
                $storedName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            }
            $path = $file->storeAs($dir, $storedName);

            $jsm->documents()->create([
                'filename' => $originalName,
                'filepath' => $path,
            ]);
        }
    }

    /**
     * Aturan validasi baris item (dipakai bareng store() & update()).
     */
    private function itemValidationRules(): array
    {
        return [
            'items' => 'nullable|array',
            'items.*.article' => 'required_with:items|string|max:100',
            'items.*.shiji_code' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.disc_nominal' => 'nullable|numeric|min:0',
            'items.*.promo_disc' => 'nullable|numeric|min:0|max:100',
            'items.*.reg' => 'required_with:items|numeric|min:0',
            'items.*.promo' => 'nullable|numeric|min:0',
            'items.*.sales' => 'nullable|array',
            'items.*.sales.*' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Baris item sekarang dikirim sebagai 1 field JSON (items_json), bukan ratusan
     * field form terpisah (items[0][article], items[0][reg], dst) -- dokumen besar
     * (banyak baris x banyak toko) bisa nembus limit max_input_vars PHP (default
     * 1000) dan datanya kepotong diam-diam tanpa error kalau masih pakai field
     * per-baris. Aturan validasi tiap baris tetap sama persis (itemValidationRules),
     * cuma sumber datanya pindah dari array form ke hasil decode JSON.
     */
    private function decodeAndValidateItems(Request $request): array
    {
        $items = json_decode($request->input('items_json', ''), true);
        if (! is_array($items)) {
            $items = [];
        }

        Validator::make(['items' => $items], $this->itemValidationRules())->validate();

        return $items;
    }

    /**
     * DISC NOMINAL dan PROMO DISC saling eksklusif per baris item.
     */
    private function assertItemsDiscMutuallyExclusive(array $items): void
    {
        foreach ($items as $index => $item) {
            $discNominal = $item['disc_nominal'] ?? null;
            $promoDisc = $item['promo_disc'] ?? null;
            $hasDisc = $discNominal !== null && $discNominal !== '' && (float) $discNominal > 0;
            $hasPromo = $promoDisc !== null && $promoDisc !== '' && (float) $promoDisc > 0;

            if ($hasDisc && $hasPromo) {
                $baris = $index + 1;
                throw ValidationException::withMessages([
                    "items.{$index}.disc_nominal" => "Baris item #{$baris}: DISC NOMINAL dan PROMO DISC tidak boleh diisi bersamaan.",
                ]);
            }
        }
    }

    /**
     * Simpan ulang seluruh baris item (hapus lalu insert ulang) beserta
     * breakdown Sales/Value per toko, dan kembalikan total nominal dokumen
     * (SUM value_total semua item). Null kalau tidak ada item sama sekali,
     * supaya nominal manual yang sudah diposting tetap dipakai apa adanya.
     */
    private function persistItems(Jsm $jsm, array $itemsInput, array $tokoIds): ?float
    {
        if (empty($itemsInput)) {
            return null;
        }

        $jsm->items()->delete();

        $total = 0;

        foreach ($itemsInput as $index => $itemInput) {
            $discNominal = $itemInput['disc_nominal'] ?? null;
            $promoDisc = $itemInput['promo_disc'] ?? null;
            $reg = (float) ($itemInput['reg'] ?? 0);

            $claim = ClaimCalculator::claim(
                ($discNominal !== null && $discNominal !== '') ? (float) $discNominal : null,
                ($promoDisc !== null && $promoDisc !== '') ? (float) $promoDisc : null,
                $reg
            );

            $item = $jsm->items()->create([
                'line_no' => $itemInput['line_no'] ?? ($index + 1),
                'article' => $itemInput['article'],
                'shiji_code' => $itemInput['shiji_code'] ?? null,
                'description' => $itemInput['description'] ?? null,
                'disc_nominal' => ($discNominal !== null && $discNominal !== '') ? $discNominal : null,
                'promo_disc' => ($promoDisc !== null && $promoDisc !== '') ? $promoDisc : null,
                'reg' => $reg,
                'promo' => ($itemInput['promo'] ?? null) !== '' ? ($itemInput['promo'] ?? null) : null,
                'claim' => $claim,
                'sales_total' => 0,
                'value_total' => 0,
            ]);

            $salesTotal = 0;
            $valueTotal = 0;

            foreach ($tokoIds as $tokoId) {
                $salesQty = $itemInput['sales'][$tokoId] ?? null;
                if ($salesQty === null || $salesQty === '') {
                    continue;
                }

                $salesQty = (float) $salesQty;
                $value = ClaimCalculator::value($claim, $salesQty);

                $item->stores()->create([
                    'toko_id' => $tokoId,
                    'sales_qty' => $salesQty,
                    'value' => $value,
                ]);

                $salesTotal += $salesQty;
                $valueTotal += $value;
            }

            $item->update(['sales_total' => $salesTotal, 'value_total' => $valueTotal]);
            $total += $valueTotal;
        }

        return $total;
    }

    public function edit(Request $request, Jsm $jsm){
        $year = Carbon::parse($jsm->periode_bulan)->year;
        $month = Carbon::parse($jsm->periode_bulan)->month;
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::whereNotIn('status',['nonaktif'])->get();
        $categories = Category::all();
        $tokos = Toko::all();
        $jsm->load('items.stores', 'documents');
        return view('jsm.edit', compact('jsm', 'supplierRafaksi', 'regions', 'tokos', 'categories', 'year', 'month'));
    }

    public function update(Request $request, Jsm $jsm){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required|after_or_equal:periode_awal',
            'no_raf' => 'string|required',
            'no_shiji' => 'nullable|string',
            'no_shiji_promotion' => 'nullable|string',
            'status_email' => 'in:aktif,tidak_aktif|required',
            'periode_bulan' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'required_without:items_json|nullable|numeric|min:0',
            'remarks' => 'string|nullable',
            'toko_id' => 'array|required',
            'toko_id.*' => 'exists:tokos,id',
            'document_file.*' => 'nullable|file|mimes:pdf|max:5120',
            'category_id' => 'exists:categories,id',
        ]);

        $items = $this->decodeAndValidateItems($request);
        $this->assertItemsDiscMutuallyExclusive($items);

        $tokoIds = $request->input('toko_id', []);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowed = auth()->user()->accessibleTokoIds()->toArray();
            $tokoIds = array_values(array_intersect($tokoIds, $allowed));
        }

        DB::transaction(function () use ($request, $jsm, $tokoIds, $items) {
            $jsm->update($request->except(['toko_id', 'items_json', 'document_file']));
            $jsm->tokos()->sync($tokoIds);

            $nominal = $this->persistItems($jsm, $items, $tokoIds);
            if ($nominal !== null) {
                $jsm->update(['nominal' => $nominal]);
            }

            $this->storeDocuments($jsm, $request);
        });

        ActivityLogger::logUpdate(
            $jsm,
            $jsm->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Updated Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal_formatted}"
        );

        $year = Carbon::parse($jsm->periode_bulan)->year;
        $month = Carbon::parse($jsm->periode_bulan)->month;

        return redirect()
            ->route('jsm.show_month', ['year' => $year, 'month' => $month, 'page' => $request->input('page')])
            ->with('success', 'Data Jsm berhasil diperbarui.');
    }

    public function destroy(Jsm $jsm){
        $jsm->delete();

        ActivityLogger::logDelete(
            $jsm,
            $jsm->id,
            ['supplier_name' => $jsm->supplier_name, 'nominal' => $jsm->nominal_formatted],
            "Deleted Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal_formatted}"
        );

        return redirect()->back()->with('success', 'Data JSM berhasil dihapus.');

    }

    public function exportCsv(Request $request)
    {
        $fileName = 'export_jsm.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [];
        $data = [];

        // MODE 1: Jika request memiliki parameter tahun & bulan (Export Detail dari show_month)
        if ($request->has('year') && $request->has('month')) {
            $year = $request->year;
            $month = $request->month;
            $fileName = "detail_jsm_{$year}_{$month}.csv";
            
            $columns = ['No', 'No. RAF', 'Kode Supplier', 'Nama Supplier', 'Region', 'Store', 'Periode Awal', 'Periode Akhir', 'Nominal'];
            
            $periodeStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $periodeEnd = (clone $periodeStart)->addMonth();

            $query = Jsm::where('periode_bulan', '>=', $periodeStart)
                ->where('periode_bulan', '<', $periodeEnd)
                ->orderBy('periode_awal', 'asc')
                ->orderBy('periode_akhir', 'asc');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('toko_id')) {
                $query->whereHas('tokos', function($q) use ($request) {
                    $q->where('tokos.id', $request->toko_id);
                });
            }

            if (! auth()->user()->hasGlobalCompanyAccess()) {
                $ids = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($ids)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereHas('tokos', function($q) use ($ids) {
                        $q->whereIn('tokos.id', $ids);
                    });
                }
            }

            $jsms = $query->get();

            foreach ($jsms as $index => $row) {
                $data[] = [
                    $index + 1,
                    $row->no_raf,
                    $row->supplier_code,
                    $row->supplier_name,
                    $row->store,
                    $row->daftar_toko_formatted,
                    $row->periode_awal,
                    $row->periode_akhir,
                    $row->nominal
                ];
            }
        } 
        // MODE 2: Jika tidak ada parameter (Export Summary dari Index)
        else {
            $fileName = "rekap_jsm_all.csv";
            $columns = ['Tahun', 'Bulan', 'Total Transaksi', 'Total Nominal'];

            $jsmGroups = Jsm::selectRaw('
                    YEAR(periode_bulan) as year, 
                    MONTH(periode_awal) as month, 
                    COUNT(*) as total_data, 
                    SUM(nominal) as total_nominal
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            foreach ($jsmGroups as $group) {
                $data[] = [
                    $group->year,
                    Carbon::create()->month($group->month)->locale('id')->format('F'),
                    $group->total_data,
                    $group->total_nominal
                ];
            }
        }

        // Proses Generate File CSV
        $headers["Content-Disposition"] = "attachment; filename=$fileName";
        
        $callback = function() use($columns, $data) {
            $file = fopen('php://output', 'w');
            
            // Opsional: Tambahkan separator untuk mengenali format Excel Indonesia (titik koma)
            // fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF))); 
            
            fputcsv($file, $columns);

            foreach ($data as $item) {
                fputcsv($file, $item);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        // Tangkap parameter dari URL (bisa ada isinya, bisa juga kosong)
        $year = $request->year;
        $month = $request->month;
        $categoryId = $request->category_id;
        $tokoId = $request->toko_id;

        // Toko yang boleh dipakai untuk export dibatasi sesuai akses user
        $tokoQuery = Toko::whereNotIn('status', ['nonaktif']);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowedTokoIds = auth()->user()->accessibleTokoIds()->toArray();
            $stores = empty($allowedTokoIds) ? collect() : $tokoQuery->whereIn('id', $allowedTokoIds)->get();
        } else {
            $stores = $tokoQuery->get();
        }

        // Tentukan nama file secara dinamis berdasarkan parameter
        if ($year && $month) {
            $fileName = 'Detail_Jsm_Report_'. $year . '_' . $month . '.xlsx';
        } elseif ($year) {
            $fileName = 'Rekap_Jsm_Report_'. $year . '.xlsx';
        } else {
            $fileName = 'Rekap_Jsm_Report_All.xlsx';
        }

        // PENTING: Masukkan $year dan $month ke dalam kurung kelas export-nya
        return Excel::download(new DetailJsmReport($year, $month, $stores, $categoryId, $tokoId), $fileName);
    }

    public function viewExcel(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $categoryId = $request->category_id;
        $tokoId = $request->toko_id;

        // Toko yang boleh dipakai untuk export dibatasi sesuai akses user
        $tokoQuery = Toko::whereNotIn('status', ['nonaktif']);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $allowedTokoIds = auth()->user()->accessibleTokoIds()->toArray();
            $stores = empty($allowedTokoIds) ? collect() : $tokoQuery->whereIn('id', $allowedTokoIds)->get();
        } else {
            $stores = $tokoQuery->get();
        }

        // Pakai logika yang sama persis dengan export Excel (DetailJsmReport::view())
        // -- bukan dihitung ulang di sini, supaya $data/$isDetail/hideTotal selalu
        // konsisten dan tetap menghormati filter categoryId/tokoId di mode rekap juga.
        return (new DetailJsmReport($year, $month, $stores, $categoryId, $tokoId))->view();
    }

    public function printPdf(Request $request){
        $year = $request->year;
        $month = $request->month;

        if($year && $month){
            $periodeStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $periodeEnd = (clone $periodeStart)->addMonth();

            $query = Jsm::with(['tokos','documents'])
                    ->where('periode_bulan', '>=', $periodeStart)
                    ->where('periode_bulan', '<', $periodeEnd)
                    ->orderBy('periode_awal', 'asc')
                    ->orderBy('periode_akhir', 'asc');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('toko_id')) {
                $query->whereHas('tokos', function($q) use ($request) {
                    $q->where('tokos.id', $request->toko_id);
                });
            }

            if (! auth()->user()->hasGlobalCompanyAccess()) {
                $ids = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($ids)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereHas('tokos', function($q) use ($ids) {
                        $q->whereIn('tokos.id', $ids);
                    });
                }
            }

            $data = $query->get();
                    $isDetail = true;
        }
        else {
            $year = $this->year ?? date('Y');
            $isDetail = false;
            $allStores = Toko::all();
            $allCategories = Category::all();

            // 1. Bangun Subquery Bulan Statis
            $bulanSubquery = "(SELECT 1 AS id_bulan, 'JANUARI' AS nama_bulan 
                                UNION ALL SELECT 2, 'FEBRUARI' UNION ALL SELECT 3, 'MARET' 
                                UNION ALL SELECT 4, 'APRIL' UNION ALL SELECT 5, 'MEI' 
                                UNION ALL SELECT 6, 'JUNI' UNION ALL SELECT 7, 'JULI' 
                                UNION ALL SELECT 8, 'AGUSTUS' UNION ALL SELECT 9, 'SEPTEMBER' 
                                UNION ALL SELECT 10, 'OKTOBER' UNION ALL SELECT 11, 'NOVEMBER' 
                                UNION ALL SELECT 12, 'DESEMBER') AS m_bulan";

            $finalData = collect();

            $combinedStoreValues = \App\Services\RekapMatrixQueryBuilder::combinedStoreValuesSql(
                'jsm', 'jsm_id', 'jsm_items', 'jsm_item_stores', 'jsm_item_id', 'jsm_toko'
            );

            // 2. Loop per Kategori
            foreach ($allCategories as $category) {
            // 1. Bangun SELECT Fields
            $selectFields = [
                "'{$category->nama_kategori}' AS Kategori",
                "m_bulan.id_bulan AS urutan_bulan",
                "m_tahun.tahun AS Tahun",
                "m_bulan.nama_bulan AS Periode"
            ];

            foreach ($allStores as $store) {
                $aliasToko = str_replace('GL ', '', $store->nama_toko);
                // Tambahkan filter category_id langsung di dalam CASE
                $selectFields[] = "SUM(CASE WHEN csv.toko_nama = '{$store->nama_toko}' AND csv.category_id = {$category->id} THEN csv.val ELSE 0 END) AS `{$aliasToko}`";
            }
            // Filter total juga harus spesifik kategori
            $selectFields[] = "SUM(CASE WHEN csv.category_id = {$category->id} THEN IFNULL(csv.val, 0) ELSE 0 END) AS TOTAL";

            // 2. Query Utama -- csv = nilai per-toko gabungan (item riil kalau ada,
            // fallback ke nominal lama buat dokumen yang belum punya baris item)
            $categoryData = DB::table(DB::raw($bulanSubquery))
                ->crossJoin(DB::raw("(SELECT DISTINCT YEAR(periode_bulan) AS tahun FROM jsm WHERE periode_bulan IS NOT NULL) AS m_tahun"))
                ->leftJoin(DB::raw($combinedStoreValues), function($join) {
                    $join->on(DB::raw('csv.mo'), '=', 'm_bulan.id_bulan')
                        ->on(DB::raw('csv.yr'), '=', 'm_tahun.tahun');
                })
                ->selectRaw(implode(', ', $selectFields))
                ->where('m_tahun.tahun', $year)
                ->groupBy('m_tahun.tahun', 'm_bulan.id_bulan', 'm_bulan.nama_bulan')
                ->orderBy('m_bulan.id_bulan', 'ASC')
                ->get();

            $finalData = $finalData->concat($categoryData);

                // Baris Pembatas (Sekat 99)
                $pembatasArray = [
                    'Kategori'     => '',
                    'urutan_bulan' => 99,
                    'Tahun'        => null,
                    'Periode'      => "--- AKHIR REKAP {$category->nama_kategori} --- \n",
                    'TOTAL'        => ''
                ];
                
                foreach ($allStores as $store) {
                    $aliasToko = str_replace('GL ', '', $store->nama_toko);
                    $pembatasArray[$aliasToko] = '';
                }
                $finalData->push((object) $pembatasArray);
            }

            // 3. Menghitung GRAND TOTAL
            $grandTotalArray = [
                'Kategori'     => 'GRAND TOTAL',
                'urutan_bulan' => 100,
                'Tahun'        => null,
                'Periode'      => 'TOTAL KESELURUHAN'
            ];

            foreach ($allStores as $store) {
                $aliasToko = str_replace('GL ', '', $store->nama_toko);
                // Menjumlahkan kolom toko dari data yang bukan baris sekat (urutan_bulan < 99)
                $grandTotalArray[$aliasToko] = $finalData->where('urutan_bulan', '<', 99)->sum($aliasToko);
            }
            $grandTotalArray['TOTAL'] = $finalData->where('urutan_bulan', '<', 99)->sum('TOTAL');

            $finalData->push((object) $grandTotalArray);

            $stores = $allStores;
            $data = $finalData;
        }

        // Load view
        $pdf = Pdf::loadView('jsm.exports_excel', compact('data', 'isDetail', 'year', 'month', 'stores'));

        if($year && $month){
            return $pdf->setPaper('A4', 'landscape')->stream('jsm-report' . $year . '-' . $month .'.pdf');
        } else{
            return $pdf->setPaper('A4', 'landscape')->stream('jsm-report-all.pdf');
        }
    }

    public function renew(Request $request, Jsm $jsm)
    {
        // 1. Validasi dinamis dengan membandingkan langsung ke data awal di database
        $request->validate([
            'periode_bulan' => 'required|date'
        ]);

        // 2. Lakukan update data terlebih dahulu ke database
        $jsm->update($request->all());

        // 3. AMBIL DATA SETELAH UPDATE (Agar mendapatkan tahun & bulan yang baru diinput)
        // Gunakan ->format('m') agar bulan tetap bernilai 2 digit (01-12) sesuai rute Laravel umum
        $year = Carbon::parse($jsm->periode_bulan)->format('Y');
        $month = Carbon::parse($jsm->periode_bulan)->format('m');

        // 4. Redirect aman ke halaman indeks bulan baru
        return redirect()
            ->route('jsm.show_month', ['year' => $year, 'month' => $month])
            ->with('success', 'Jsm dengan ID: ' . $jsm->id . ' berhasil diupdate.');
    }

    public function renewIndex(Request $request){
        $id = $request->query('id');

        if (!$id) {
            abort(404, 'Parameter ID tidak ditemukan.');
        }

        $jsm = Jsm::findOrFail($id);
        return view('jsm.renew_index', compact('jsm'));
    }

    public function downloadDocument(JsmDocument $document)
    {
        if (! Storage::exists($document->filepath)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return Storage::download($document->filepath, $document->filename);
    }

    public function deleteDocument(JsmDocument $document)
    {
        Storage::delete($document->filepath);
        $document->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function statusAktif(Jsm $jsm){
        $jsm->update([
            'status_email' => 'aktif',
        ]);

        ActivityLogger::logUpdate(
            $jsm,
            $jsm->id,
            ['status_email' => 'aktif'],
            "Updated Jsm #{$jsm->id}: status_email set to aktif"
        );

        return redirect()->back()->with('success', 'Status email berhasil diubah menjadi aktif.');
    }

    public function statusTidakAktif(Jsm $jsm){
        $jsm->update([
            'status_email' => 'tidak_aktif',
        ]);
        
        ActivityLogger::logUpdate(
            $jsm,
            $jsm->id,
            ['status_email' => 'tidak_aktif'],
            "Updated Jsm #{$jsm->id}: status_email set to tidak_aktif"
        );

        return redirect()->back()->with('success', 'Status email berhasil diubah menjadi tidak aktif.');
    }
}
