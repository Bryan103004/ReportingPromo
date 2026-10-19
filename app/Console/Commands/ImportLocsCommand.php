<?php

namespace App\Console\Commands;

use App\Mail\ImportLocInfo;
use App\Models\ImportLocLog;
use App\Models\Loc;
use App\Models\NotificationRecipient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Cache master data toko sekali di memori (menggunakan id_alias sebagai key)
        // Menghindari ribuan query N+1 ke database pada saat iterasi data CSV
        $tokoMap = DB::table('tokos')
            ->join('regions', 'tokos.region_id', '=', 'regions.id')
            ->whereNotNull('tokos.id_alias')
            ->select('tokos.id as toko_id', 'tokos.id_alias', 'regions.nama_region')
            ->get()
            ->keyBy(function ($item) {
                return trim($item->id_alias);
            });

        // Helper untuk merapikan format tanggal agar sesuai dengan tipe data date MySQL (Y-m-d)
        $formatDate = function ($date) {
            $date = trim((string) $date);
            if (empty($date)) return null;
            try {
                // Format CSV adalah d/m/Y atau j/n/Y (misal: 30/4/2025)
                return Carbon::createFromFormat('j/n/Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    return Carbon::parse($date)->format('Y-m-d');
                } catch (\Exception $ex) {
                    return null;
                }
            }
        };

        foreach ($csvFiles as $filePath) {
            $filename = basename($filePath);
            $startedAt = now();
            $this->info("Memproses file: {$filename}...");

            $fileHandle = fopen($filePath, 'r');

            if (!$fileHandle) {
                $errorMsg = "Gagal membuka file: {$filename}";
                $this->error($errorMsg);

                ImportLocLog::create([
                    'filename'      => $filename,
                    'total_rows'    => 0,
                    'success_count' => 0,
                    'skipped_count' => 0,
                    'failed_count'  => 0,
                    'status'        => 'failed',
                    'error_message' => $errorMsg,
                    'failed_rows'   => null,
                    'started_at'    => $startedAt,
                    'finished_at'   => now(),
                ]);

                $this->sendAlertEmail($filename, 0, 0, 0, 0, null, $errorMsg);
                continue;
            }

            // Lewati baris header jika ada
            fgetcsv($fileHandle, 0, ';');

            $stagingBatch = [];
            $failedRows = [];
            $totalRows = 0;
            $successCount = 0;
            $skippedCount = 0;
            $lineNumber = 1; // Baris 1 adalah header

            // Kosongkan tabel staging (inbound) sebelum memproses file ini
            DB::table('inbound')->truncate();

            while (($row = fgetcsv($fileHandle, 0, ';')) !== false) {
                $lineNumber++;
                $totalRows++;

                $supplierCode = trim((string) ($row[0] ?? ''));
                $supplierName = trim((string) ($row[3] ?? ''));
                $noRaf        = trim((string) ($row[7] ?? ''));
                $tokoAliasRaw = trim((string) ($row[10] ?? ''));

                // Validasi 1: Jika no_raf atau supplier_code kosong, skip baris ini (sebagai error)
                if (empty($noRaf) || empty($supplierCode)) {
                    $failedRows[] = [
                        'line'       => $lineNumber,
                        'no_raf'     => $noRaf ?: '(kosong)',
                        'supplier'   => $supplierName ? "{$supplierName} ({$supplierCode})" : ($supplierCode ?: '(kosong)'),
                        'toko_alias' => $tokoAliasRaw ?: '-',
                        'reason'     => 'Kolom wajib (no_raf atau supplier_code) kosong',
                    ];
                    continue;
                }

                // Validasi 2: Jika data LOC dengan no_raf ini SUDAH ADA di database, SKIP (tidak ditimpa)
                if (Loc::where('no_raf', $noRaf)->exists()) {
                    $skippedCount++;
                    $this->line(" -> SUDAH ADA: no_raf [{$noRaf}] sudah ada di database, dilewati (skip).");
                    continue;
                }

                // Validasi 3: Jika toko_id (alias) kosong di CSV, skip baris ini (sebagai error)
                if (empty($tokoAliasRaw)) {
                    $failedRows[] = [
                        'line'       => $lineNumber,
                        'no_raf'     => $noRaf,
                        'supplier'   => $supplierName ? "{$supplierName} ({$supplierCode})" : $supplierCode,
                        'toko_alias' => '(kosong)',
                        'reason'     => 'Kolom toko_id pada CSV kosong',
                    ];
                    continue;
                }

                // Validasi 4: Cek apakah id_alias toko ada di database master tokos
                $matchedToko = $tokoMap->get($tokoAliasRaw);
                if (!$matchedToko) {
                    $failedRows[] = [
                        'line'       => $lineNumber,
                        'no_raf'     => $noRaf,
                        'supplier'   => $supplierName ? "{$supplierName} ({$supplierCode})" : $supplierCode,
                        'toko_alias' => $tokoAliasRaw,
                        'reason'     => "Kode alias toko '{$tokoAliasRaw}' tidak terdaftar di database master toko",
                    ];
                    continue;
                }

                $tokoIdAsli = $matchedToko->toko_id;
                $namaRegionStore = $matchedToko->nama_region;

                // Masukkan data valid ke batch staging inbound
                $stagingBatch[] = [
                    'supplier_code'    => $supplierCode,
                    'category_id'      => $row[1] ?? null,
                    'reminder_id'      => $row[2] ?? null,
                    'supplier_name'    => $supplierName,
                    'periode_awal'     => $formatDate($row[4] ?? null),
                    'periode_akhir'    => $formatDate($row[5] ?? null),
                    'periode_bulan'    => $formatDate($row[6] ?? null),
                    'no_raf'           => $noRaf,
                    'no_raf_referensi' => $row[8] ?? null,
                    'store'            => $namaRegionStore ?? ($row[9] ?? null),
                    'toko_id'          => $tokoAliasRaw,
                    'nominal'          => $row[11] ?? null,
                    'remarks'          => $row[12] ?? null,
                    'created_date'     => $formatDate($row[13] ?? null),
                    'created_time'     => $row[14] ?? null,
                    'updated_date'     => $formatDate($row[15] ?? null),
                    'updated_time'     => $row[16] ?? null,
                    'status_email'     => $row[17] ?? null,
                ];

                if (count($stagingBatch) >= 500) {
                    DB::table('inbound')->insert($stagingBatch);
                    $stagingBatch = [];
                }

                // Simpan data BARU ke tabel utama `locs`
                try {
                    $loc = Loc::create([
                        'supplier_code'    => $supplierCode,
                        'category_id'      => $row[1] ?? null,
                        'reminder_id'      => $row[2] ?? null,
                        'supplier_name'    => $supplierName,
                        'periode_awal'     => $formatDate($row[4] ?? null),
                        'periode_akhir'    => $formatDate($row[5] ?? null),
                        'periode_bulan'    => $formatDate($row[6] ?? null),
                        'no_raf'           => $noRaf,
                        'no_raf_referensi' => $row[8] ?? null,
                        'status_email'     => $row[17] ?? null,
                        'store'            => $namaRegionStore ?? ($row[9] ?? null),
                        'nominal'          => $row[11] ?? null,
                        'remarks'          => $row[12] ?? null,
                        'created_date'     => $formatDate($row[13] ?? null),
                        'created_time'     => $row[14] ?? null,
                        'updated_date'     => $formatDate($row[15] ?? null),
                        'updated_time'     => $row[16] ?? null,
                    ]);

                    // Hubungkan ke tabel pivot locs_toko
                    $loc->tokos()->syncWithoutDetaching([$tokoIdAsli]);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Gagal menyimpan no_raf {$noRaf}: " . $e->getMessage());
                    $failedRows[] = [
                        'line'       => $lineNumber,
                        'no_raf'     => $noRaf,
                        'supplier'   => $supplierName ? "{$supplierName} ({$supplierCode})" : $supplierCode,
                        'toko_alias' => $tokoAliasRaw,
                        'reason'     => 'Database error saat simpan: ' . $e->getMessage(),
                    ];
                }
            }

            if (!empty($stagingBatch)) {
                DB::table('inbound')->insert($stagingBatch);
            }

            fclose($fileHandle);

            $failedCount = count($failedRows);
            $this->info("-> Selesai membaca file. Total: {$totalRows} | Baru: {$successCount} | Sudah Ada (Skip): {$skippedCount} | Gagal/Error: {$failedCount}");

            // Tentukan status import
            if ($totalRows === 0) {
                $status = 'failed';
                $statusMessage = 'File kosong tanpa data';
            } elseif ($failedCount === 0) {
                $status = 'success';
                $statusMessage = null;
            } elseif ($successCount > 0 || $skippedCount > 0) {
                $status = 'partial_failed';
                $statusMessage = "Sebagian data ({$failedCount} baris) gagal diproses karena error";
            } else {
                $status = 'failed';
                $statusMessage = "Seluruh baris ({$failedCount} baris) gagal diproses";
            }

            // Simpan riwayat eksekusi ke tabel log database
            ImportLocLog::create([
                'filename'      => $filename,
                'total_rows'    => $totalRows,
                'success_count' => $successCount,
                'skipped_count' => $skippedCount,
                'failed_count'  => $failedCount,
                'status'        => $status,
                'error_message' => $statusMessage,
                'failed_rows'   => !empty($failedRows) ? $failedRows : null,
                'started_at'    => $startedAt,
                'finished_at'   => now(),
            ]);

            // Jika ada baris yang gagal, kirimkan email laporan rekap ke daftar penerima dinamis
            if ($failedCount > 0) {
                $this->warn("Ada {$failedCount} baris yang gagal diproses. Mengirim email laporan...");
                $this->sendAlertEmail($filename, $totalRows, $successCount, $skippedCount, $failedCount, $failedRows, $statusMessage);
            }

            // Pindahkan file CSV ke folder outbound dengan timestamp
            $destinationPath = $outboundFolder . '/' . date('Ymd_His') . '_' . $filename;
            rename($filePath, $destinationPath);

            $this->info("-> File {$filename} selesai diproses dan dipindah ke folder outbound.");
        }

        $this->info('Semua file CSV berhasil diproses!');
        return 0;
    }

    /**
     * Kirim email pemberitahuan ke penerima yang dikonfigurasi secara dinamis
     */
    private function sendAlertEmail(
        $filename,
        $totalRows,
        $successCount,
        $skippedCount,
        $failedCount,
        $failedRows = null,
        $errorMsg = null
    ) {
        // Ambil email dari menu Web "Email Tambahan" (modul: 'import_loc') yang aktif
        $recipients = NotificationRecipient::active()
            ->forModule('import_loc')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Fallback jika belum ada yang diset di database
        if (empty($recipients)) {
            $fallback = config('mail.from.address', 'it@grandlucky.co.id');
            if ($fallback) {
                $recipients = [$fallback];
            }
        }

        if (empty($recipients)) {
            $this->warn('Tidak ada alamat email penerima yang terdaftar untuk notifikasi import LOC.');
            return;
        }

        try {
            Mail::to($recipients)->send(new ImportLocInfo(
                $filename,
                $totalRows,
                $successCount,
                $skippedCount,
                $failedCount,
                $failedRows,
                $errorMsg
            ));
            $this->info("-> Email laporan berhasil dikirim ke: " . implode(', ', $recipients));
        } catch (\Exception $e) {
            $this->error("-> Gagal mengirim email laporan: " . $e->getMessage());
            Log::error("Gagal mengirim email notifikasi import LOC: " . $e->getMessage(), [
                'filename'   => $filename,
                'recipients' => $recipients,
            ]);
        }
    }
}