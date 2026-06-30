<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            'report_file' => 'required|file|mimes:txt'
        ]);

        $path = $request->file('report_file')->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $outletCode = '';
        $outletName = '';
        $buffer = null; // Menyimpan baris pertama (SKU) sementara
        $dataToInsert = [];

        foreach ($lines as $line) {
            $text = trim($line);

            // 1. Ambil Data Outlet dari Header TXT
            if (strpos($text, 'Outlet Code :') !== false) {
                // Contoh text: "Outlet Code :    9903 GRANDLUCKY - PIK"
                $parts = explode(' ', trim(str_replace('Outlet Code :', '', $text)));
                $outletCode = $parts[0]; // 9903
                $outletName = implode(' ', array_slice($parts, 1)); // GRANDLUCKY - PIK
                continue;
            }

            // 2. Lewati baris pembatas, header, subtotal, dan grandtotal
            if (strpos($text, '---') !== false || 
                strpos($text, 'SKU#') !== false || 
                strpos($text, 'Sub Total') !== false || 
                strpos($text, 'GrandTotal') !== false ||
                strpos($text, 'Quantity|') !== false) {
                continue;
            }

            $cols = explode('|', $text);

            // 3. Deteksi Baris 1 (Berisi SKU, Nama, Size, UOM, Date)
            // Memastikan kolom 0 adalah angka SKU 11 digit [cite: 5]
            if (preg_match('/^[0-9]{11}/', $cols[0])) {
                $buffer = [
                    'sku'              => trim($cols[0]),
                    'product_name'     => trim($cols[1] ?? ''),
                    'size'             => trim($cols[2] ?? ''),
                    'uom'              => trim($cols[3] ?? ''),
                    'transaction_date' => \Carbon\Carbon::createFromFormat('d/m/Y', trim($cols[4]))->format('Y-m-d'),
                ];
            } 
            // 4. Deteksi Baris 2 (Berisi Quantity, Gross Sales, dll)
            // Cek apakah buffer terisi dan array baris kedua panjang (biasanya lebih dari 10 indeks)
            else if ($buffer !== null && count($cols) > 10) {
                
                // Helper untuk membersihkan nilai kosong dari TXT agar aman masuk ke database
                $cleanDecimal = function($val) {
                    return (float) str_replace([' ', ','], '', trim($val));
                };

                $dataToInsert[] = [
                    // Data Outlet
                    'outlet_code'      => $outletCode,
                    'outlet_name'      => $outletName,
                    
                    // Dari Baris 1 (Buffer)
                    'sku'              => $buffer['sku'],
                    'product_name'     => $buffer['product_name'],
                    'size'             => $buffer['size'],
                    'uom'              => $buffer['uom'],
                    'transaction_date' => $buffer['transaction_date'],
                    
                    // Dari Baris 2 (Membaca indeks array secara berurutan) [cite: 6]
                    'quantity'         => $cleanDecimal($cols[0] ?? 0),
                    'gross_sales'      => $cleanDecimal($cols[1] ?? 0),
                    'sls_discount'     => $cleanDecimal($cols[2] ?? 0),
                    'sales_return'     => $cleanDecimal($cols[3] ?? 0),
                    'sales_incl_tax'   => $cleanDecimal($cols[4] ?? 0),
                    'pct_sales'        => trim($cols[5] ?? ''),
                    'sales_tax'        => $cleanDecimal($cols[6] ?? 0),
                    'sales_excl_tax'   => $cleanDecimal($cols[7] ?? 0),
                    'cogs'             => $cleanDecimal($cols[8] ?? 0),
                    'gp_amount'        => $cleanDecimal($cols[9] ?? 0),
                    'pct_gp'           => trim($cols[10] ?? ''),
                    'contribu'         => trim($cols[11] ?? ''),
                    'soh'              => $cleanDecimal($cols[12] ?? 0),
                    'md1'              => trim($cols[13] ?? ''),
                    'desc_m1'          => trim($cols[14] ?? ''),
                    'md2'              => trim($cols[15] ?? ''),
                    'desc_m2'          => trim($cols[16] ?? ''),
                    'md3'              => trim($cols[17] ?? ''),
                    'desc_m3'          => trim($cols[18] ?? ''),
                    'md4'              => trim($cols[19] ?? ''),
                    'desc_m4'          => trim($cols[20] ?? ''),
                    'sku_2'            => trim($cols[21] ?? ''),
                    'supl_no'          => trim($cols[22] ?? ''),
                    'supplier_name'    => trim($cols[23] ?? ''),
                    
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                
                // Kosongkan buffer
                $buffer = null; 
            }
        }

        // Insert massal agar lebih cepat
        if (!empty($dataToInsert)) {
            // Chunk insert untuk mencegah memory limit jika file terlalu besar
            $chunks = array_chunk($dataToInsert, 500);
            foreach ($chunks as $chunk) {
                SalesReport::insert($chunk);
            }
        }

        return redirect()->back()->with('success', 'Data berhasil di-upload dan dibersihkan.');
    }

public function getWeeklyReport()
    {
        // Set ke hari Minggu ini (akhir minggu)
        $endDate = Carbon::now()->endOfWeek(); 
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
}