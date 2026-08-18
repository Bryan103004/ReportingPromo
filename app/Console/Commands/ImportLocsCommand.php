<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Loc;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportLocsCommand extends Command
{
    protected $signature = 'import:locs';
    protected $description = 'Otomatis memproses semua file CSV di folder public/inbound dan memindahkannya ke public/outbound';

    public function handle()
    {
        $inboundFolder = public_path('inbound');
        $outboundFolder = public_path('outbound');

        // Pastikan folder outbound ada, jika belum buat otomatis
        if (!file_exists($outboundFolder)) {
            mkdir($outboundFolder, 0755, true);
        }

        // 1. Ambil semua file berformat .csv di dalam folder public/inbound
        $files = glob($inboundFolder . '/*.csv');

        $csvFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'csv';
        });

        if (empty($csvFiles)) {
            $this->info('Tidak ada file CSV yang ditemukan untuk diproses.');
            return 0;
        }

        foreach ($csvFiles as $filePath) {
            $filename = basename($filePath);
            $this->info("Memproses file: {$filename}...");

            $fileHandle = fopen($filePath, 'r');

            if (!$fileHandle) {
                $this->error("Gagal membuka file: {$filename}");
                continue;
            }

            // Lewati baris header jika ada
            fgetcsv($fileHandle);
            
            $batchData = [];
            $rowCount = 0;
            $failedRows = []; 
            $lineNumber = 1;  

            // Kosongkan tabel staging (inbound) sebelum memproses file ini
            DB::table('inbound')->truncate();

            // Fungsi helper untuk merapikan format tanggal agar sesuai dengan tipe data date MySQL (Y-m-d)
            $formatDate = function($date) {
                $date = trim($date);
                if (empty($date)) return null;
                try {
                    // Karena format di CSV adalah d/m/Y (misal: 30/4/2025)
                    return Carbon::createFromFormat('j/n/Y', $date)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        return Carbon::parse($date)->format('Y-m-d');
                    } catch (\Exception $ex) {
                        return null;
                    }
                }
            };

            // 2. Masukkan SEMUA data mentah ke tabel staging
            while (($row = fgetcsv($fileHandle)) !== false) {
                $lineNumber++;

                // Validasi: Jika no_raf (index 7) atau supplier_code (index 0) kosong, catat sebagai data gagal
                if (empty($row[7]) || empty($row[0])) {
                    $failedRows[] = [
                        'line' => $lineNumber,
                        'data' => implode(', ', $row),
                        'reason' => 'Kolom utama (no_raf atau supplier_code) kosong'
                    ];
                    continue;
                }

                $batchData[] = [
                    'supplier_code'    => $row[0] ?? null,
                    'category_id'      => $row[1] ?? null,
                    'reminder_id'      => $row[2] ?? null,
                    'supplier_name'    => $row[3] ?? null,
                    'periode_awal'     => $formatDate($row[4]),
                    'periode_akhir'    => $formatDate($row[5]),
                    'periode_bulan'    => $formatDate($row[6]),
                    'no_raf'           => $row[7] ?? null,
                    'no_raf_referensi' => $row[8] ?? null,
                    'toko_id'          => $row[9] ?? null,
                    'store'            => $row[10] ?? null,   
                    'nominal'          => $row[11] ?? null,
                    'remarks'          => $row[12] ?? null,
                    'created_date'     => $formatDate($row[13]),
                    'created_time'     => $row[14] ?? null,
                    'updated_date'     => $formatDate($row[15]),
                    'updated_time'     => $row[16] ?? null,
                    'status_email'     => $row[17] ?? null,
                ];

                $rowCount++;

                if (count($batchData) >= 500) {
                    DB::table('inbound')->insert($batchData);
                    $batchData = [];
                }
            }

            if (!empty($batchData)) {
                DB::table('inbound')->insert($batchData);
            }

            fclose($fileHandle);
            $this->info("-> Berhasil memasukkan {$rowCount} baris ke staging.");

            // Tampilkan laporan jika ada baris yang gagal masuk ke tabel inbound
            if (!empty($failedRows)) {
                $this->warn("Perhatian: Ada " . count($failedRows) . " baris yang TIDAK masuk ke tabel inbound:");
                foreach ($failedRows as $fail) {
                    $this->line(" - Baris ke-{$fail['line']} | Alasan: {$fail['reason']}");
                    $this->line("   Isi Data: {$fail['data']}");
                }
            }

            // 3. Pindahkan data dari Inbound ke Tabel Utama (locs) & Hubungkan ke Pivot Toko
            $inboundRows = DB::table('inbound')->get();

            foreach ($inboundRows as $row) {
                if (empty($row->no_raf)) {
                    continue;
                }

                // ==========================================
                // DEBUGGING TOKO_ID
                // ==========================================
                $this->info("Mengecek no_raf: {$row->no_raf} dengan toko_id di CSV: [{$row->toko_id}]");

                // A. Terjemahkan ID Alias toko dari CSV menjadi Primary Key (id) asli tabel tokos
                $tokoIdAsli = null;
                $namaRegionStore = null;

                if (!empty($row->toko_id)) {
                    // Gunakan trim untuk menghindari masalah spasi tersembunyi
                    $cleanTokoId = trim($row->toko_id);
                    
                    $tokoData = DB::table('tokos')
                        ->join('regions', 'tokos.region_id', '=', 'regions.id')
                        ->where('tokos.id_alias', $cleanTokoId)
                        ->select('tokos.id as toko_id', 'regions.nama_region')
                        ->first();
                                        
                    if ($tokoData) {
                        $tokoIdAsli = $tokoData->toko_id;
                        $namaRegionStore = $tokoData->nama_region; // Contoh: "Jakarta"
                        $this->line(" -> DITEMUKAN: Toko ID {$tokoIdAsli}, Region/Store: {$namaRegionStore}");
                    } else {
                        $this->error(" -> TIDAK KETEMU: toko_id [{$cleanTokoId}] tidak ada di tabel tokos!");
                    }
                } else {
                    $this->warn(" -> KOSONG: Kolom toko_id pada baris ini kosong.");
                }

                // B. Simpan atau Update data ke tabel utama `locs`
                $loc = Loc::updateOrCreate(
                    ['no_raf' => $row->no_raf],
                    [
                        'supplier_code'    => $row->supplier_code,
                        'category_id'      => $row->category_id,
                        'reminder_id'      => $row->reminder_id,
                        'supplier_name'    => $row->supplier_name,
                        'periode_awal'     => $formatDate($row->periode_awal),
                        'periode_akhir'    => $formatDate($row->periode_akhir), // Pastikan ini menggunakan variabel dari data staging
                        'periode_bulan'    => $formatDate($row->periode_bulan),
                        'no_raf_referensi' => $row->no_raf_referensi,
                        'status_email'     => $row->status_email,
                        'store'            => $namaRegionStore ?? $row->store,
                        'nominal'          => $row->nominal,
                        'remarks'          => $row->remarks,
                        'created_date'     => $row->created_date,
                        'created_time'     => $row->created_time,
                        'updated_date'     => $row->updated_date,
                        'updated_time'     => $row->updated_time,
                    ]
                );

                // C. Catat relasi ke tabel pivot menggunakan ID asli toko
                if ($tokoIdAsli) {
                    $loc->tokos()->syncWithoutDetaching([$tokoIdAsli]);
                    $this->line(" -> Berhasil sync ke tabel pivot locs_toko.");
                } else {
                    $this->error(" -> Gagal sync karena \$tokoIdAsli bernilai null.");
                }
            }

            // 4. Cleanup: Kosongkan tabel inbound & pindahkan file fisik ke folder outbound
            // DB::table('inbound')->truncate();
            
            $destinationPath = $outboundFolder . '/' . date('Ymd_His') . '_' . $filename;
            rename($filePath, $destinationPath);

            $this->info("-> File {$filename} selesai diproses dan dipindah ke folder outbound.");
        }

        $this->info('Semua file CSV berhasil diproses!');
        return 0;
    }
}