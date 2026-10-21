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

                $this->sendAlertEmail($filename, 0, 0, 0, 0, 0, null, $errorMsg);
                continue;
            }

            // Lewati baris header jika ada
            fgetcsv($fileHandle, 0, ';');

            $stagingBatch = [];
            $failedRows = [];
            $totalRows = 0;
            $successCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $lineNumber = 1; // Baris 1 adalah header

            // Cache data LOC yang sudah ada di database ke memori:
            // Menyimpan id, nominal, ppn, dan amount_incl_ppn berdasarkan no_raf
            $existingLocs = [];
            foreach (Loc::select('id', 'no_raf', 'nominal', 'ppn', 'amount_incl_ppn')->cursor() as $loc) {
                $noRafKey = trim((string) $loc->no_raf);
                if ($noRafKey !== '') {
                    $existingLocs[$noRafKey] = [
                        'id'              => $loc->id,
                        'nominal'         => (float) $loc->nominal,
                        'ppn'             => (float) $loc->ppn,
                        'amount_incl_ppn' => (float) $loc->amount_incl_ppn,
                    ];
                }
            }

            // Helper pembersih angka desimal dari spasi (misal '  3000000')
            $cleanDecimal = function ($val) {
                if ($val === null || $val === '') return 0;
                $val = trim((string) $val);
                $val = str_replace([' ', ','], ['', '.'], $val);
                return is_numeric($val) ? (float) $val : 0;
            };

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

                // Validasi 2: Jika toko_id (alias) kosong di CSV, skip baris ini (sebagai error)
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

                // Validasi 3: Cek apakah id_alias toko ada di database master tokos
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

                // Normalisasi status_email dari CSV:
                // Tabel `inbound` menggunakan enum ('aktif', 'nonaktif')
                // Tabel `locs` menggunakan enum ('aktif', 'tidak_aktif')
                $statusEmailRaw = strtolower(trim((string) ($row[19] ?? '')));
                $isInactive = in_array($statusEmailRaw, ['nonaktif', 'tidak_aktif', 'tidak aktif', '0']);
                $statusEmailInbound = $isInactive ? 'nonaktif' : 'aktif';
                $statusEmailLoc     = $isInactive ? 'tidak_aktif' : 'aktif';

                $nominal       = $cleanDecimal($row[11] ?? null);
                $ppn           = $cleanDecimal($row[12] ?? null);
                $amountInclPpn = $cleanDecimal($row[13] ?? null);

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
                    'nominal'          => $nominal,
                    'ppn'              => $ppn,
                    'amount_incl_ppn'  => $amountInclPpn,
                    'remarks'          => $row[14] ?? null,
                    'created_date'     => $formatDate($row[15] ?? null),
                    'created_time'     => $row[16] ?? null,
                    'updated_date'     => $formatDate($row[17] ?? null),
                    'updated_time'     => $row[18] ?? null,
                    'status_email'     => $statusEmailInbound,
                ];

                if (count($stagingBatch) >= 500) {
                    DB::table('inbound')->insert($stagingBatch);
                    $stagingBatch = [];
                }

                // Cek apakah data LOC dengan no_raf ini SUDAH ADA di database
                if (isset($existingLocs[$noRaf])) {
                    $existing = $existingLocs[$noRaf];
                    $currentNominal       = (float) ($existing['nominal'] ?? 0);
                    $currentPpn           = (float) ($existing['ppn'] ?? 0);
                    $currentAmountInclPpn = (float) ($existing['amount_incl_ppn'] ?? 0);

                    // Cek apakah Nominal, PPN, atau Amount Incl PPN berubah / berbeda
                    $isDiff = abs($currentNominal - (float) $nominal) > 0.009
                           || abs($currentPpn - (float) $ppn) > 0.009
                           || abs($currentAmountInclPpn - (float) $amountInclPpn) > 0.009;

                    if ($isDiff) {
                        try {
                            // Nimpa data lama dengan data promo terbaru dari CSV
                            // (Tetap mempertahankan status invoice, status email, approval, dan dokumen lampiran)
                            DB::table('locs')->where('id', $existing['id'])->update([
                                'supplier_code'    => $supplierCode,
                                'category_id'      => $row[1] ?? null,
                                'reminder_id'      => $row[2] ?? null,
                                'supplier_name'    => $supplierName,
                                'periode_awal'     => $formatDate($row[4] ?? null),
                                'periode_akhir'    => $formatDate($row[5] ?? null),
                                'periode_bulan'    => $formatDate($row[6] ?? null),
                                'no_raf_referensi' => $row[8] ?? null,
                                'store'            => $namaRegionStore ?? ($row[9] ?? null),
                                'nominal'          => $nominal,
                                'ppn'              => $ppn,
                                'amount_incl_ppn'  => $amountInclPpn,
                                'remarks'          => $row[14] ?? null,
                                'updated_date'     => $formatDate($row[17] ?? null),
                                'updated_time'     => $row[18] ?? null,
                                'updated_at'       => now(),
                            ]);

                            // Hubungkan ke tabel pivot locs_toko jika belum ada
                            DB::table('locs_toko')->updateOrInsert(
                                ['loc_id' => $existing['id'], 'toko_id' => $tokoIdAsli],
                                ['updated_at' => now()]
                            );

                            // Perbarui cache di memori
                            $existingLocs[$noRaf]['nominal']         = (float) $nominal;
                            $existingLocs[$noRaf]['ppn']             = (float) $ppn;
                            $existingLocs[$noRaf]['amount_incl_ppn'] = (float) $amountInclPpn;

                            $updatedCount++;
                        } catch (\Exception $e) {
                            $this->error("Gagal menimpa data no_raf {$noRaf}: " . $e->getMessage());
                            $failedRows[] = [
                                'line'       => $lineNumber,
                                'no_raf'     => $noRaf,
                                'supplier'   => $supplierName ? "{$supplierName} ({$supplierCode})" : $supplierCode,
                                'toko_alias' => $tokoAliasRaw,
                                'reason'     => 'Database error saat menimpa data: ' . $e->getMessage(),
                            ];
                        }
                    } else {
                        // Nilai sama persis, dilewati (skip)
                        $skippedCount++;
                    }

                    continue;
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
                        'status_email'     => $statusEmailLoc,
                        'store'            => $namaRegionStore ?? ($row[9] ?? null),
                        'nominal'          => $nominal,
                        'ppn'              => $ppn,
                        'amount_incl_ppn'  => $amountInclPpn,
                        'remarks'          => $row[14] ?? null,
                        'created_date'     => $formatDate($row[15] ?? null),
                        'created_time'     => $row[16] ?? null,
                        'updated_date'     => $formatDate($row[17] ?? null),
                        'updated_time'     => $row[18] ?? null,
                    ]);

                    // Hubungkan ke tabel pivot locs_toko
                    $loc->tokos()->syncWithoutDetaching([$tokoIdAsli]);
                    $existingLocs[$noRaf] = [
                        'id'              => $loc->id,
                        'nominal'         => (float) $nominal,
                        'ppn'             => (float) $ppn,
                        'amount_incl_ppn' => (float) $amountInclPpn,
                    ];
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
            $this->info("-> Selesai membaca file. Total: {$totalRows} | Baru: {$successCount} | Diupdate: {$updatedCount} | Sudah Ada (Skip): {$skippedCount} | Gagal/Error: {$failedCount}");

            // Tentukan status import
            if ($totalRows === 0) {
                $status = 'failed';
                $statusMessage = 'File kosong tanpa data';
            } elseif ($failedCount === 0) {
                $status = 'success';
                $statusMessage = null;
            } elseif ($successCount > 0 || $updatedCount > 0 || $skippedCount > 0) {
                $status = 'partial_failed';
                $statusMessage = "Sebagian data ({$failedCount} baris) gagal diproses karena error";
            } else {
                $status = 'failed';
                $statusMessage = "Seluruh baris ({$failedCount} baris) gagal diproses";
            }

            // Batasi data failed_rows yang disimpan ke tabel log agar payload JSON tidak melebihi batasan max_allowed_packet MySQL
            $storedFailedRows = null;
            if (!empty($failedRows)) {
                if (count($failedRows) > 100) {
                    $storedFailedRows = array_slice($failedRows, 0, 100);
                    $storedFailedRows[] = [
                        'line'       => '-',
                        'no_raf'     => '-',
                        'supplier'   => '-',
                        'toko_alias' => '-',
                        'reason'     => '... dan ' . (count($failedRows) - 100) . ' baris error lainnya (dipotong untuk keamanan ukuran log database).',
                    ];
                } else {
                    $storedFailedRows = $failedRows;
                }
            }

            // Simpan riwayat eksekusi ke tabel log database
            ImportLocLog::create([
                'filename'      => $filename,
                'total_rows'    => $totalRows,
                'success_count' => $successCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'failed_count'  => $failedCount,
                'status'        => $status,
                'error_message' => $statusMessage,
                'failed_rows'   => $storedFailedRows,
                'started_at'    => $startedAt,
                'finished_at'   => now(),
            ]);

            // Jika ada baris yang gagal, kirimkan email laporan rekap ke daftar penerima dinamis
            if ($failedCount > 0) {
                $this->warn("Ada {$failedCount} baris yang gagal diproses. Mengirim email laporan...");
                $emailFailedRows = count($failedRows) > 100 ? array_slice($failedRows, 0, 100) : $failedRows;
                $this->sendAlertEmail($filename, $totalRows, $successCount, $updatedCount, $skippedCount, $failedCount, $emailFailedRows, $statusMessage);
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
        $updatedCount = 0,
        $skippedCount = 0,
        $failedCount = 0,
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
                $updatedCount,
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