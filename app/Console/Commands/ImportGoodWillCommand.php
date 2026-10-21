<?php

namespace App\Console\Commands;

use App\Models\InvoiceGoodwill;
use App\Models\InvoiceLog;
use App\Models\Loc;
use App\Models\PaymentGoodwill;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportGoodWillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:goodwill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis memproses file CSV di folder inbound invoice dan inbound payment Goodwill, memasukkan ke staging & database utama, lalu memindahkannya ke outbound';

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
        // 1. Setup folder paths
        $inboundInvoiceDirs = array_values(array_filter([
            public_path('inbound-invoice'),
            public_path('inboud-invoice')
        ], 'file_exists'));

        $outboundInvoiceFolder = public_path('outbound-invoice');
        if (!file_exists($outboundInvoiceFolder)) {
            mkdir($outboundInvoiceFolder, 0755, true);
        }

        $inboundPaymentFolder = public_path('inbound-payment');
        if (!file_exists($inboundPaymentFolder)) {
            mkdir($inboundPaymentFolder, 0755, true);
        }
        $outboundPaymentFolder = public_path('outbound-payment');
        if (!file_exists($outboundPaymentFolder)) {
            mkdir($outboundPaymentFolder, 0755, true);
        }

        // Helper tanggal
        $formatDate = function ($date) {
            $date = trim((string) $date);
            if (empty($date)) return null;
            foreach (['d-m-Y', 'd/m/Y', 'j/n/Y', 'j-n-Y', 'Y-m-d'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $date)->format('Y-m-d');
                } catch (\Exception $e) {
                    // continue
                }
            }
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $ex) {
                return null;
            }
        };

        // Helper waktu
        $formatTime = function ($time) {
            $time = trim((string) $time);
            return $time !== '' ? $time : null;
        };

        // Helper angka/desimal
        $formatNumber = function ($num) {
            $num = trim((string) $num);
            if ($num === '') return 0.00;
            $num = str_replace([',', ' '], ['', ''], $num);
            return is_numeric($num) ? (float) $num : 0.00;
        };

        // Helper untuk auto-update invoice_status = 1 pada tabel locs jika ada SO_No (No. RAF)
        $syncLocInvoiceStatus = function ($soNo) {
            if (empty($soNo)) return;

            $loc = Loc::where('no_raf', $soNo)->first();
            if ($loc && empty($loc->invoice_status)) {
                $now = now();
                $loc->update([
                    'invoice_status'     => 1,
                    'invoice_done_count' => DB::raw('COALESCE(invoice_done_count, 0) + 1'),
                    'invoice_done_at'    => $now,
                    'invoice_done_by'    => 'System (Goodwill)',
                ]);

                InvoiceLog::create([
                    'document_type' => 'loc',
                    'document_id'   => $loc->id,
                    'no_raf'        => $soNo,
                    'status'        => 'done',
                    'user_id'       => null,
                    'user_name'     => 'System (Goodwill)',
                    'action_at'     => $now,
                ]);
            }
        };

        // -------------------------------------------------------------
        // A. PROSES FILE INVOICE
        // -------------------------------------------------------------
        $invoiceFiles = [];
        foreach ($inboundInvoiceDirs as $dir) {
            $found = glob($dir . '/*.csv');
            if (!empty($found)) {
                $invoiceFiles = array_merge($invoiceFiles, $found);
            }
        }

        if (empty($invoiceFiles)) {
            $this->info('[Invoice] Tidak ada file CSV invoice yang ditemukan di inbound.');
        } else {
            $this->info('[Invoice] Ditemukan ' . count($invoiceFiles) . ' file CSV invoice.');

            // Truncate staging table inbound_invoice_goodwills
            DB::table('inbound_invoice_goodwills')->truncate();

            foreach ($invoiceFiles as $filePath) {
                $filename = basename($filePath);
                $this->info("Memproses invoice: {$filename}...");

                $fileHandle = fopen($filePath, 'r');
                if (!$fileHandle) {
                    $this->error("Gagal membuka file: {$filename}");
                    continue;
                }

                // Header CSV
                $header = fgetcsv($fileHandle, 0, '|');

                $stagingBatch = [];
                $totalRows    = 0;
                $newCount     = 0;
                $updatedCount = 0;
                $skippedCount = 0;

                while (($row = fgetcsv($fileHandle, 0, '|')) !== false) {
                    // Cek jika baris kosong
                    if (count($row) < 2 || empty(trim((string) $row[1]))) {
                        continue;
                    }

                    $totalRows++;

                    $client          = trim((string) ($row[0] ?? ''));
                    $invNo           = trim((string) ($row[1] ?? ''));
                    $invDate         = $formatDate($row[2] ?? null);
                    $invTime         = $formatTime($row[3] ?? null);
                    $grandTotal      = $formatNumber($row[4] ?? 0);
                    $lastUpdatedDate = $formatDate($row[5] ?? null);
                    $lastUpdatedTime = $formatTime($row[6] ?? null);
                    $status          = trim((string) ($row[7] ?? ''));
                    $soNo            = trim((string) ($row[8] ?? ''));
                    $billDate        = $formatDate($row[9] ?? null);
                    $billTime        = $formatTime($row[10] ?? null);
                    $paid            = trim((string) ($row[11] ?? ''));

                    // Masukkan ke batch staging
                    $stagingBatch[] = [
                        'client'            => $client,
                        'inv_no'            => $invNo,
                        'inv_date'          => $invDate,
                        'inv_time'          => $invTime,
                        'grand_total'       => $grandTotal,
                        'last_updated_date' => $lastUpdatedDate,
                        'last_updated_time' => $lastUpdatedTime,
                        'status'            => $status,
                        'so_no'             => $soNo,
                        'bill_date'         => $billDate,
                        'bill_time'         => $billTime,
                        'paid'              => $paid,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];

                    if (count($stagingBatch) >= 500) {
                        DB::table('inbound_invoice_goodwills')->insert($stagingBatch);
                        $stagingBatch = [];
                    }

                    // Cek keberadaan data lama di invoice_goodwills
                    $existing = DB::table('invoice_goodwills')->where('inv_no', $invNo)->first();

                    if ($existing) {
                        // Cek apakah persis sama
                        $isIdentical = (
                            $existing->client === $client &&
                            $existing->inv_date === $invDate &&
                            $existing->inv_time === $invTime &&
                            (float) $existing->grand_total === (float) $grandTotal &&
                            $existing->last_updated_date === $lastUpdatedDate &&
                            $existing->last_updated_time === $lastUpdatedTime &&
                            $existing->status === $status &&
                            $existing->so_no === $soNo &&
                            $existing->bill_date === $billDate &&
                            $existing->bill_time === $billTime &&
                            $existing->paid === $paid
                        );

                        if ($isIdentical) {
                            $skippedCount++;
                        } else {
                            // Proteksi status Paid: jika di database sudah Paid ('Y'), data dikunci / tidak boleh ditimpa
                            if ($existing->paid === 'Y') {
                                $skippedCount++;
                            } else {
                                DB::table('invoice_goodwills')->where('id', $existing->id)->update([
                                    'client'            => $client,
                                    'inv_date'          => $invDate,
                                    'inv_time'          => $invTime,
                                    'grand_total'       => $grandTotal,
                                    'last_updated_date' => $lastUpdatedDate,
                                    'last_updated_time' => $lastUpdatedTime,
                                    'status'            => $status,
                                    'so_no'             => $soNo,
                                    'bill_date'         => $billDate,
                                    'bill_time'         => $billTime,
                                    'paid'              => $paid,
                                    'updated_at'        => now(),
                                ]);
                                $updatedCount++;
                            }
                        }
                    } else {
                        // Insert invoice baru
                        DB::table('invoice_goodwills')->insert([
                            'client'            => $client,
                            'inv_no'            => $invNo,
                            'inv_date'          => $invDate,
                            'inv_time'          => $invTime,
                            'grand_total'       => $grandTotal,
                            'last_updated_date' => $lastUpdatedDate,
                            'last_updated_time' => $lastUpdatedTime,
                            'status'            => $status,
                            'so_no'             => $soNo,
                            'bill_date'         => $billDate,
                            'bill_time'         => $billTime,
                            'paid'              => $paid,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                        $newCount++;
                    }

                    // Kunci status data LOC (invoice_status = 1) jika belum dikunci
                    $syncLocInvoiceStatus($soNo);
                }

                if (!empty($stagingBatch)) {
                    DB::table('inbound_invoice_goodwills')->insert($stagingBatch);
                }

                fclose($fileHandle);

                $destPath = $outboundInvoiceFolder . '/' . date('Ymd_His') . '_' . $filename;
                rename($filePath, $destPath);
                $this->info("-> Invoice {$filename} selesai: Total {$totalRows} | Baru: {$newCount} | Diupdate: {$updatedCount} | Di-skip: {$skippedCount} (Dipindah ke outbound).");
            }
        }

        // -------------------------------------------------------------
        // B. PROSES FILE PAYMENT
        // -------------------------------------------------------------
        $paymentFiles = glob($inboundPaymentFolder . '/*.csv');

        if (empty($paymentFiles)) {
            $this->info('[Payment] Tidak ada file CSV payment yang ditemukan di inbound.');
        } else {
            $this->info('[Payment] Ditemukan ' . count($paymentFiles) . ' file CSV payment.');

            // Truncate staging table inbound_payment_goodwills
            DB::table('inbound_payment_goodwills')->truncate();

            foreach ($paymentFiles as $filePath) {
                $filename = basename($filePath);
                $this->info("Memproses payment: {$filename}...");

                $fileHandle = fopen($filePath, 'r');
                if (!$fileHandle) {
                    $this->error("Gagal membuka file: {$filename}");
                    continue;
                }

                // Header CSV
                $header = fgetcsv($fileHandle, 0, '|');

                $stagingBatch = [];
                $totalRows    = 0;
                $newCount     = 0;
                $updatedCount = 0;
                $skippedCount = 0;

                while (($row = fgetcsv($fileHandle, 0, '|')) !== false) {
                    // Cek jika baris kosong
                    if (count($row) < 12 || empty(trim((string) ($row[11] ?? '')))) {
                        continue;
                    }

                    $totalRows++;

                    $client          = trim((string) ($row[0] ?? ''));
                    $invNo           = trim((string) ($row[1] ?? ''));
                    $invDate         = $formatDate($row[2] ?? null);
                    $invTime         = $formatTime($row[3] ?? null);
                    $grandTotal      = $formatNumber($row[4] ?? 0);
                    $lastUpdatedDate = $formatDate($row[5] ?? null);
                    $lastUpdatedTime = $formatTime($row[6] ?? null);
                    $status          = trim((string) ($row[7] ?? ''));
                    $soNo            = trim((string) ($row[8] ?? ''));
                    $billDate        = $formatDate($row[9] ?? null);
                    $billTime        = $formatTime($row[10] ?? null);
                    $allocationNo    = trim((string) ($row[11] ?? ''));
                    $allocationDate  = $formatDate($row[12] ?? null);
                    $allocationTime  = $formatTime($row[13] ?? null);
                    $allocationAmt   = $formatNumber($row[14] ?? 0);
                    $paymentNo       = trim((string) ($row[15] ?? ''));
                    $paymentDate     = $formatDate($row[16] ?? null);
                    $paymentTime     = $formatTime($row[17] ?? null);
                    $paid            = trim((string) ($row[18] ?? ''));

                    // Masukkan ke staging batch
                    $stagingBatch[] = [
                        'client'            => $client,
                        'inv_no'            => $invNo,
                        'inv_date'          => $invDate,
                        'inv_time'          => $invTime,
                        'grand_total'       => $grandTotal,
                        'last_updated_date' => $lastUpdatedDate,
                        'last_updated_time' => $lastUpdatedTime,
                        'status'            => $status,
                        'so_no'             => $soNo,
                        'bill_date'         => $billDate,
                        'bill_time'         => $billTime,
                        'allocation_no'     => $allocationNo,
                        'allocation_date'   => $allocationDate,
                        'allocation_time'   => $allocationTime,
                        'allocation_amt'    => $allocationAmt,
                        'payment_no'        => $paymentNo,
                        'payment_date'      => $paymentDate,
                        'payment_time'      => $paymentTime,
                        'paid'              => $paid,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];

                    if (count($stagingBatch) >= 500) {
                        DB::table('inbound_payment_goodwills')->insert($stagingBatch);
                        $stagingBatch = [];
                    }

                    // Cek data lama di payment_goodwills berdasarkan allocation_no
                    $existing = DB::table('payment_goodwills')->where('allocation_no', $allocationNo)->first();

                    if ($existing) {
                        // Cek apakah persis sama
                        $isIdentical = (
                            $existing->client === $client &&
                            $existing->inv_no === $invNo &&
                            $existing->inv_date === $invDate &&
                            $existing->inv_time === $invTime &&
                            (float) $existing->grand_total === (float) $grandTotal &&
                            $existing->last_updated_date === $lastUpdatedDate &&
                            $existing->last_updated_time === $lastUpdatedTime &&
                            $existing->status === $status &&
                            $existing->so_no === $soNo &&
                            $existing->bill_date === $billDate &&
                            $existing->bill_time === $billTime &&
                            $existing->allocation_date === $allocationDate &&
                            $existing->allocation_time === $allocationTime &&
                            (float) $existing->allocation_amt === (float) $allocationAmt &&
                            $existing->payment_no === $paymentNo &&
                            $existing->payment_date === $paymentDate &&
                            $existing->payment_time === $paymentTime &&
                            $existing->paid === $paid
                        );

                        if ($isIdentical) {
                            $skippedCount++;
                        } else {
                            // Proteksi status Paid: jika di database sudah Paid ('Y'), data dikunci / tidak boleh ditimpa
                            if ($existing->paid === 'Y') {
                                $skippedCount++;
                            } else {
                                DB::table('payment_goodwills')->where('id', $existing->id)->update([
                                    'client'            => $client,
                                    'inv_no'            => $invNo,
                                    'inv_date'          => $invDate,
                                    'inv_time'          => $invTime,
                                    'grand_total'       => $grandTotal,
                                    'last_updated_date' => $lastUpdatedDate,
                                    'last_updated_time' => $lastUpdatedTime,
                                    'status'            => $status,
                                    'so_no'             => $soNo,
                                    'bill_date'         => $billDate,
                                    'bill_time'         => $billTime,
                                    'allocation_date'   => $allocationDate,
                                    'allocation_time'   => $allocationTime,
                                    'allocation_amt'    => $allocationAmt,
                                    'payment_no'        => $paymentNo,
                                    'payment_date'      => $paymentDate,
                                    'payment_time'      => $paymentTime,
                                    'paid'              => $paid,
                                    'updated_at'        => now(),
                                ]);
                                $updatedCount++;
                            }
                        }
                    } else {
                        // Insert payment baru
                        DB::table('payment_goodwills')->insert([
                            'client'            => $client,
                            'inv_no'            => $invNo,
                            'inv_date'          => $invDate,
                            'inv_time'          => $invTime,
                            'grand_total'       => $grandTotal,
                            'last_updated_date' => $lastUpdatedDate,
                            'last_updated_time' => $lastUpdatedTime,
                            'status'            => $status,
                            'so_no'             => $soNo,
                            'bill_date'         => $billDate,
                            'bill_time'         => $billTime,
                            'allocation_no'     => $allocationNo,
                            'allocation_date'   => $allocationDate,
                            'allocation_time'   => $allocationTime,
                            'allocation_amt'    => $allocationAmt,
                            'payment_no'        => $paymentNo,
                            'payment_date'      => $paymentDate,
                            'payment_time'      => $paymentTime,
                            'paid'              => $paid,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                        $newCount++;
                    }

                    // Kunci status data LOC (invoice_status = 1) jika belum dikunci
                    $syncLocInvoiceStatus($soNo);
                }

                if (!empty($stagingBatch)) {
                    DB::table('inbound_payment_goodwills')->insert($stagingBatch);
                }

                fclose($fileHandle);

                $destPath = $outboundPaymentFolder . '/' . date('Ymd_His') . '_' . $filename;
                rename($filePath, $destPath);
                $this->info("-> Payment {$filename} selesai: Total {$totalRows} | Baru: {$newCount} | Diupdate: {$updatedCount} | Di-skip: {$skippedCount} (Dipindah ke outbound).");
            }
        }

        $this->info('Selesai memproses semua data Goodwill!');
        return 0;
    }
}
