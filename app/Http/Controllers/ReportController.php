<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // Menampilkan halaman upload & tabel
    public function index()
    {
        return view('report.index');
    }

    // Memproses file TXT
    public function store(Request $request)
    {
        $request->validate([
            'report_file'   => 'required|array',
            'report_file.*' => 'file|mimes:txt|max:5120',
        ]);

        $outletCode = '';
        $outletName = '';
        $bufferLine = ''; // Menyimpan string gabungan
        $dataToInsert = [];

        // Fungsi bantuan (Closure) agar kodenya rapi saat mengekstrak data
        $processBuffer = function($lineStr) use (&$dataToInsert, &$outletCode, &$outletName) {
            $cols = explode('|', $lineStr);
            
            // Jika kolom kurang dari 15, berarti baris ini masih cacat/bukan transaksi
            if (count($cols) < 15) return; 

            $cleanDecimal = function($val) {
                return (float) str_replace([' ', ','], '', trim($val));
            };

            // Handling tanggal agar tidak error jika format mendadak aneh
            try {
                $trxDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($cols[4]))->format('Y-m-d');
            } catch (\Exception $e) {
                $trxDate = now()->format('Y-m-d');
            }

            $dataToInsert[] = [
                'outlet_code'      => $outletCode,
                'outlet_name'      => $outletName,
                'sku'              => trim($cols[0]),
                'product_name'     => trim($cols[1] ?? ''),
                'size'             => trim($cols[2] ?? ''),
                'uom'              => trim($cols[3] ?? ''),
                'transaction_date' => $trxDate,
                
                // Karena baris 1 & 2 sudah kita gabung, indeksnya tinggal dilanjutkan
                'quantity'         => $cleanDecimal($cols[5] ?? 0),
                'gross_sales'      => $cleanDecimal($cols[6] ?? 0),
                'sls_discount'     => $cleanDecimal($cols[7] ?? 0),
                'sales_return'     => $cleanDecimal($cols[8] ?? 0),
                'sales_incl_tax'   => $cleanDecimal($cols[9] ?? 0),
                'pct_sales'        => trim($cols[10] ?? ''),
                'sales_tax'        => $cleanDecimal($cols[11] ?? 0),
                'sales_excl_tax'   => $cleanDecimal($cols[12] ?? 0),
                'cogs'             => $cleanDecimal($cols[13] ?? 0),
                'gp_amount'        => $cleanDecimal($cols[14] ?? 0),
                'pct_gp'           => trim($cols[15] ?? ''),
                'contribu'         => trim($cols[16] ?? ''),
                'soh'              => $cleanDecimal($cols[17] ?? 0),
                'md1'              => trim($cols[18] ?? ''),
                'desc_m1'          => trim($cols[19] ?? ''),
                'md2'              => trim($cols[20] ?? ''),
                'desc_m2'          => trim($cols[21] ?? ''),
                'md3'              => trim($cols[22] ?? ''),
                'desc_m3'          => trim($cols[23] ?? ''),
                'md4'              => trim($cols[24] ?? ''),
                'desc_m4'          => trim($cols[25] ?? ''),
                'sku_2'            => trim($cols[26] ?? ''),
                'supl'          => trim($cols[27] ?? ''),
                'supplier_name'    => trim($cols[28] ?? ''),
                
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        };

        // LOOP 1: Eksekusi setiap file TXT yang di-upload
        $files = $request->file('report_file');
        
        foreach ($files as $file) {
            $path = $file->getRealPath();
            ini_set('auto_detect_line_endings', true);
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Reset buffer & outlet info untuk setiap dokumen baru
            $outletCode = '';
            $outletName = '';
            $bufferLine = ''; 

            // LOOP 2: Eksekusi per baris dalam satu dokumen
            foreach ($lines as $line) {
                $text = trim($line);

                if (strpos($text, 'Outlet Code :') !== false) {
                    $parts = explode(' ', trim(str_replace('Outlet Code :', '', $text)));
                    $outletCode = $parts[0]; 
                    $outletName = implode(' ', array_slice($parts, 1));
                    continue;
                }

                // Skip tulisan sampah
                if (strpos($text, '---') !== false || 
                    strpos($text, 'SKU#') !== false || 
                    strpos($text, 'Sub Total') !== false || 
                    strpos($text, 'GrandTotal') !== false ||
                    strpos($text, 'Quantity|') !== false ||
                    empty($text)) {
                    continue;
                }

                // KUNCI UTAMA: Regex ini mendeteksi angka 10-15 digit yang diakhiri garis '|' (PASTI SKU)
                if (preg_match('/^\d{10,15}\|/', $text)) {
                    if ($bufferLine !== '') {
                        $processBuffer($bufferLine);
                    }
                    $bufferLine = $text;
                } 
                else if ($bufferLine !== '') {
                    $bufferLine .= $text;
                }
            }
            
            // Proses produk terakhir di file ini
            if ($bufferLine !== '') {
                $processBuffer($bufferLine);
            }
        }

        // Insert massal agar lebih cepat
        if (!empty($dataToInsert)) {
            $chunks = array_chunk($dataToInsert, 500);
            foreach ($chunks as $chunk) {
                SalesReport::insert($chunk);
            }
        }

        return redirect()->back()->with('success', count($files) . ' dokumen TXT berhasil di-upload dan dibersihkan.');
    }

    public function getWeeklyReport()
    {
        // Cari tanggal transaksi paling terakhir (paling baru) yang ada di database
        $latestDate = SalesReport::max('transaction_date');

        // Jika database belum ada datanya, fallback ke hari ini. Jika ada, gunakan tanggal tersebut.
        $endDate = $latestDate ? Carbon::parse($latestDate)->endOfWeek() : Carbon::now()->endOfWeek(); 
        
        // Tarik mundur 4 minggu ke hari Senin
        $startDate = $endDate->copy()->subWeeks(4)->startOfWeek(); 

        $reports = SalesReport::select(
                'outlet_name',
                'transaction_date',
                DB::raw('SUM(gross_sales) as total_sales')
            )
            ->whereBetween('transaction_date', [$startDate, $endDate])
            // Filter MySQL: 1=Minggu, 6=Jumat, 7=Sabtu
            ->whereRaw('DAYOFWEEK(transaction_date) IN (1, 6, 7)') 
            ->groupBy('outlet_name', 'transaction_date')
            ->orderBy('outlet_name')
            ->orderBy('transaction_date')
            ->get();

        // Data siap dikirim ke view/JSON untuk dirender oleh tabel
        return response()->json($reports);
    }

    public function getWeeklyMatrix()
    {
        // 1. Cari tanggal transaksi paling terakhir (paling baru) di database
        $latestDate = SalesReport::max('transaction_date');

        // Jika DB kosong, fallback ke hari ini. Jika ada, gunakan tanggal terbaru sebagai D-Day.
        $dDayEnd = $latestDate ? Carbon::parse($latestDate)->endOfWeek() : Carbon::now()->endOfWeek();
        $dDayStart = $dDayEnd->copy()->startOfWeek();
        
        // 2. Tarik mundur per minggu menggunakan Carbon (Otomatis tembus pergantian bulan & tahun)
        $w1_end = $dDayEnd->copy()->subWeeks(1);
        $w1_start = $w1_end->copy()->startOfWeek();
        
        $w2_end = $dDayEnd->copy()->subWeeks(2);
        $w2_start = $w2_end->copy()->startOfWeek();
        
        $w3_end = $dDayEnd->copy()->subWeeks(3);
        $w3_start = $w3_end->copy()->startOfWeek();
        
        $w4_end = $dDayEnd->copy()->subWeeks(4);
        $w4_start = $w4_end->copy()->startOfWeek();

        // 3. Buat String Periode menggunakan PHP Carbon agar formatnya lebih rapi
        // Hasilnya misalnya: "15 May 2026 to 14 Jun 2026"
        $periodeString = $w4_start->format('d M Y') . ' - ' . $dDayEnd->format('d M Y');

        // 4. Query dengan Conditional Aggregation (Pivot)
        $reports = SalesReport::select(
            'outlet_code',
            'outlet_name',            
            
            // Masukkan variabel string langsung ke dalam raw query
            DB::raw("'$periodeString' as periode"),
            
            // Pivot Minggu 4
            DB::raw("SUM(CASE WHEN transaction_date BETWEEN '{$w4_start->toDateString()}' AND '{$w4_end->toDateString()}' THEN gross_sales ELSE 0 END) as week_4_sales"),
            // Pivot Minggu 3 
            DB::raw("SUM(CASE WHEN transaction_date BETWEEN '{$w3_start->toDateString()}' AND '{$w3_end->toDateString()}' THEN gross_sales ELSE 0 END) as week_3_sales"),
            // Pivot Minggu 2 
            DB::raw("SUM(CASE WHEN transaction_date BETWEEN '{$w2_start->toDateString()}' AND '{$w2_end->toDateString()}' THEN gross_sales ELSE 0 END) as week_2_sales"),
            // Pivot Minggu 1 
            DB::raw("SUM(CASE WHEN transaction_date BETWEEN '{$w1_start->toDateString()}' AND '{$w1_end->toDateString()}' THEN gross_sales ELSE 0 END) as week_1_sales"),
            // Pivot D-Day
            DB::raw("SUM(CASE WHEN transaction_date BETWEEN '{$dDayStart->toDateString()}' AND '{$dDayEnd->toDateString()}' THEN gross_sales ELSE 0 END) as d_day_sales"),
            // Total Semua (5 Minggu)
            DB::raw("SUM(gross_sales) as total_sales")
        )
        ->whereBetween('transaction_date', [$w4_start, $dDayEnd]) // Rentang ditarik dari Mgg 4 sampai D-Day
        ->whereRaw('DAYOFWEEK(transaction_date) IN (1, 6, 7)') // 1=Minggu, 6=Jumat, 7=Sabtu
        ->groupBy('outlet_code', 'outlet_name', 'periode')
        ->get();

        return response()->json([
            'data' => $reports
        ]);
    }

    public function exportExcel(Request $request)
    {
        $kode_outlet = $request->kode_outlet;
        $year = $request->year;
        $month = $request->month;
        

        // 1. Siapkan Query Pengecekan
        $checkQuery = SalesReport::query();

        if($year){
            $checkQuery->whereYear('transaction_date', $year);
        }

        if($month){
            $checkQuery->whereMonth('transaction_date', $month);
        }

        if($kode_outlet){
            $checkQuery->where('outlet_code', $kode_outlet);
        }

        if (!$checkQuery->exists()) {
            // Kembali ke halaman sebelumnya dengan membawa pesan error
            return redirect()->back()->withErrors([
                'export' => "Gagal mengunduh! Tidak ada data transaksi untuk Outlet {$kode_outlet} pada periode {$month}-{$year}."
            ]);
        }

        // Kirim variabel ke dalam Class Export
        return Excel::download(
            new SalesReportExport($year, $month, $kode_outlet), 
            'Sales_Report_' . ($kode_outlet ?: 'ALL') . '_' . $year . '-' . $month . '.xlsx'
        );
    }
}