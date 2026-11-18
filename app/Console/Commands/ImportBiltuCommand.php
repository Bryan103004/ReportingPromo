<?php

namespace App\Console\Commands;

use App\Models\Biltu;
use App\Models\ImportBiltuLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportBiltuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:biltu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis memproses semua file CSV di folder public/inbound-biltu dan memindahkannya ke public/outbound-biltu setelah selesai diproses.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inboundFolder = public_path('inbound-biltu');
        $outboundFolder = public_path('outbound-biltu');

        if (!file_exists($outboundFolder)) {
            mkdir($outboundFolder, 0755, true);
        }

        if (!file_exists($inboundFolder)) {
            mkdir($inboundFolder, 0755, true);
        }

        $files = glob($inboundFolder . '/*.csv');

        $csvFiles = array_filter($files, function ($file) {
            return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'csv';
        });

        if (empty($csvFiles)) {
            $this->info('Tidak ada file CSV yang ditemukan untuk diproses di ' . $inboundFolder);
            return 0;
        }

        foreach ($csvFiles as $filePath) {
            $filename = basename($filePath);
            $startedAt = now();
            $this->info("Memproses file: {$filename}...");

            $fileHandle = fopen($filePath, 'r');
            if (!$fileHandle) {
                $errorMsg = "Gagal membuka file: {$filename}";
                $this->error($errorMsg);

                ImportBiltuLog::create([
                    'filename'      => $filename,
                    'total_rows'    => 0,
                    'success_count' => 0,
                    'updated_count' => 0,
                    'skipped_count' => 0,
                    'failed_count'  => 0,
                    'status'        => 'failed',
                    'error_message' => $errorMsg,
                    'failed_rows'   => null,
                    'started_at'    => $startedAt,
                    'finished_at'   => now(),
                ]);
                continue;
            }

            // Baca baris pertama untuk deteksi delimiter dan header
            $firstLine = fgets($fileHandle);
            if ($firstLine === false) {
                fclose($fileHandle);
                continue;
            }

            $delimiter = strpos($firstLine, '|') !== false ? '|' : (strpos($firstLine, ';') !== false ? ';' : ',');

            // Truncate staging table inbound_biltus setiap kali proses batch file
            DB::table('inbound_biltus')->truncate();

            // Cache data biltu yang sudah ada ke memori: key = C_BPartner_ID + '|' + value
            $existingBiltu = [];
            foreach (Biltu::cursor() as $b) {
                $key = trim((string) $b->C_BPartner_ID) . '|' . trim((string) $b->value);
                $existingBiltu[$key] = [
                    'id'         => $b->id,
                    'client'     => $b->client,
                    'name'       => $b->name,
                    'name2'      => $b->name2,
                    'taxid'      => $b->taxid,
                    'Address1'   => $b->Address1,
                    'Address2'   => $b->Address2,
                    'Address3'   => $b->Address3,
                    'Address4'   => $b->Address4,
                    'City'       => $b->City,
                    'regionName' => $b->regionName,
                    'postal'     => $b->postal,
                    'vendor_no'  => $b->vendor_no,
                    'phone'      => $b->phone,
                    'phone2'     => $b->phone2,
                    'NITKU'      => $b->NITKU,
                    'is_mba'     => $b->is_mba,
                ];
            }

            $stagingBatch = [];
            $failedRows = [];
            $totalRows = 0;
            $successCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $lineNumber = 1;

            while (($row = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
                $lineNumber++;
                if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                    continue; // baris kosong
                }

                $totalRows++;

                $client        = trim((string) ($row[0] ?? ''));
                $C_BPartner_ID = trim((string) ($row[1] ?? ''));
                $value         = trim((string) ($row[2] ?? ''));
                $name          = trim((string) ($row[3] ?? ''));
                $name2         = trim((string) ($row[4] ?? ''));
                $taxid         = trim((string) ($row[5] ?? ''));
                $address1      = trim((string) ($row[6] ?? ''));
                $address2      = trim((string) ($row[7] ?? ''));
                $address3      = trim((string) ($row[8] ?? ''));
                $address4      = trim((string) ($row[9] ?? ''));
                $city          = trim((string) ($row[10] ?? ''));
                $regionName    = trim((string) ($row[11] ?? ''));
                $postal        = trim((string) ($row[12] ?? ''));
                $vendor_no     = trim((string) ($row[13] ?? ''));
                $phone         = trim((string) ($row[14] ?? ''));
                $phone2        = trim((string) ($row[15] ?? ''));
                $nitku         = trim((string) ($row[16] ?? ''));

                if (empty($C_BPartner_ID)) {
                    $failedRows[] = [
                        'line'          => $lineNumber,
                        'C_BPartner_ID' => '(kosong)',
                        'vendor_no'     => $vendor_no,
                        'reason'        => 'C_BPartner_ID tidak boleh kosong',
                    ];
                    continue;
                }

                // Tentukan apakah MBA atau Non-MBA
                // Aturan: jika file bpartnerJKT atau value berawalan JC atau client mengandung Mitra Belanja Anda
                $isMba = (stripos($filename, 'jkt') !== false) 
                    || (str_starts_with(strtoupper($value), 'JC')) 
                    || (stripos($client, 'Mitra Belanja Anda') !== false);

                // Masukkan ke staging inbound_biltus
                $stagingBatch[] = [
                    'client'        => $client,
                    'C_BPartner_ID' => $C_BPartner_ID,
                    'value'         => $value,
                    'name'          => $name,
                    'name2'         => $name2,
                    'taxid'         => $taxid,
                    'Address1'      => $address1,
                    'Address2'      => $address2,
                    'Address3'      => $address3,
                    'Address4'      => $address4,
                    'City'          => $city,
                    'regionName'    => $regionName,
                    'postal'        => $postal,
                    'vendor_no'     => $vendor_no,
                    'phone'         => $phone,
                    'phone2'        => $phone2,
                    'NITKU'         => $nitku,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                if (count($stagingBatch) >= 500) {
                    DB::table('inbound_biltus')->insert($stagingBatch);
                    $stagingBatch = [];
                }

                // Cek data di tabel biltus utama
                $cacheKey = $C_BPartner_ID . '|' . $value;

                if (isset($existingBiltu[$cacheKey])) {
                    $existing = $existingBiltu[$cacheKey];

                    $isDiff = 
                        ($existing['client'] !== $client) ||
                        ($existing['name'] !== $name) ||
                        ($existing['name2'] !== $name2) ||
                        ($existing['taxid'] !== $taxid) ||
                        ($existing['Address1'] !== $address1) ||
                        ($existing['Address2'] !== $address2) ||
                        ($existing['Address3'] !== $address3) ||
                        ($existing['Address4'] !== $address4) ||
                        ($existing['City'] !== $city) ||
                        ($existing['regionName'] !== $regionName) ||
                        ($existing['postal'] !== $postal) ||
                        ($existing['vendor_no'] !== $vendor_no) ||
                        ($existing['phone'] !== $phone) ||
                        ($existing['phone2'] !== $phone2) ||
                        ($existing['NITKU'] !== $nitku) ||
                        ((bool)$existing['is_mba'] !== (bool)$isMba);

                    if ($isDiff) {
                        try {
                            DB::table('biltus')->where('id', $existing['id'])->update([
                                'client'     => $client,
                                'name'       => $name,
                                'name2'      => $name2,
                                'taxid'      => $taxid,
                                'Address1'   => $address1,
                                'Address2'   => $address2,
                                'Address3'   => $address3,
                                'Address4'   => $address4,
                                'City'       => $city,
                                'regionName' => $regionName,
                                'postal'     => $postal,
                                'vendor_no'  => $vendor_no,
                                'phone'      => $phone,
                                'phone2'     => $phone2,
                                'NITKU'      => $nitku,
                                'is_mba'     => $isMba,
                                'updated_at' => now(),
                            ]);

                            $existingBiltu[$cacheKey] = array_merge($existing, [
                                'client'     => $client,
                                'name'       => $name,
                                'name2'      => $name2,
                                'taxid'      => $taxid,
                                'Address1'   => $address1,
                                'Address2'   => $address2,
                                'Address3'   => $address3,
                                'Address4'   => $address4,
                                'City'       => $city,
                                'regionName' => $regionName,
                                'postal'     => $postal,
                                'vendor_no'  => $vendor_no,
                                'phone'      => $phone,
                                'phone2'     => $phone2,
                                'NITKU'      => $nitku,
                                'is_mba'     => $isMba,
                            ]);

                            $updatedCount++;
                        } catch (\Exception $e) {
                            $failedRows[] = [
                                'line'          => $lineNumber,
                                'C_BPartner_ID' => $C_BPartner_ID,
                                'value'         => $value,
                                'reason'        => 'Gagal update: ' . $e->getMessage(),
                            ];
                        }
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // Data Baru
                    try {
                        $newId = DB::table('biltus')->insertGetId([
                            'client'        => $client,
                            'C_BPartner_ID' => $C_BPartner_ID,
                            'value'         => $value,
                            'name'          => $name,
                            'name2'         => $name2,
                            'taxid'         => $taxid,
                            'Address1'      => $address1,
                            'Address2'      => $address2,
                            'Address3'      => $address3,
                            'Address4'      => $address4,
                            'City'          => $city,
                            'regionName'    => $regionName,
                            'postal'        => $postal,
                            'vendor_no'     => $vendor_no,
                            'phone'         => $phone,
                            'phone2'        => $phone2,
                            'NITKU'         => $nitku,
                            'is_mba'        => $isMba,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);

                        $existingBiltu[$cacheKey] = [
                            'id'         => $newId,
                            'client'     => $client,
                            'name'       => $name,
                            'name2'      => $name2,
                            'taxid'      => $taxid,
                            'Address1'   => $address1,
                            'Address2'   => $address2,
                            'Address3'   => $address3,
                            'Address4'   => $address4,
                            'City'       => $city,
                            'regionName' => $regionName,
                            'postal'     => $postal,
                            'vendor_no'  => $vendor_no,
                            'phone'      => $phone,
                            'phone2'     => $phone2,
                            'NITKU'      => $nitku,
                            'is_mba'     => $isMba,
                        ];

                        $successCount++;
                    } catch (\Exception $e) {
                        $failedRows[] = [
                            'line'          => $lineNumber,
                            'C_BPartner_ID' => $C_BPartner_ID,
                            'value'         => $value,
                            'reason'        => 'Gagal insert: ' . $e->getMessage(),
                        ];
                    }
                }
            }

            if (!empty($stagingBatch)) {
                DB::table('inbound_biltus')->insert($stagingBatch);
            }

            fclose($fileHandle);

            $failedCount = count($failedRows);
            $status = $failedCount === 0 ? 'success' : ($successCount + $updatedCount > 0 ? 'partial_failed' : 'failed');

            ImportBiltuLog::create([
                'filename'      => $filename,
                'total_rows'    => $totalRows,
                'success_count' => $successCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'failed_count'  => $failedCount,
                'status'        => $status,
                'error_message' => $failedCount > 0 ? "Terdapat {$failedCount} baris bermasalah." : null,
                'failed_rows'   => $failedCount > 0 ? $failedRows : null,
                'started_at'    => $startedAt,
                'finished_at'   => now(),
            ]);

            // Pindahkan file ke outbound-biltu
            $timestamp = date('Ymd_His');
            $outboundFilePath = $outboundFolder . '/' . pathinfo($filename, PATHINFO_FILENAME) . "_{$timestamp}.csv";
            @rename($filePath, $outboundFilePath);

            $this->info("Selesai memproses {$filename}: {$successCount} baru, {$updatedCount} diupdate, {$skippedCount} diskip, {$failedCount} gagal.");
        }

        return 0;
    }
}
