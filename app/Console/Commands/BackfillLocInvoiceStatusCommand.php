<?php

namespace App\Console\Commands;

use App\Models\InvoiceGoodwill;
use App\Models\InvoiceLog;
use App\Models\Loc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillLocInvoiceStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loc:backfill-invoice-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill field invoice_status = 1 pada tabel locs untuk semua data yang nomor RAF-nya sudah memiliki invoice di invoice_goodwills';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai pengecekan data LOC yang sudah memiliki invoice Goodwill...');

        // 1. Ambil semua SO_No (No. RAF) unik dari tabel invoice_goodwills
        $soNumbers = InvoiceGoodwill::whereNotNull('so_no')
            ->where('so_no', '!=', '')
            ->distinct()
            ->pluck('so_no')
            ->toArray();

        if (empty($soNumbers)) {
            $this->warn('Tidak ada data invoice di tabel invoice_goodwills.');
            return 0;
        }

        $this->info('Ditemukan ' . count($soNumbers) . ' no_raf unik di tabel invoice_goodwills.');

        // 2. Ambil data LOC yang bersangkutan dan invoice_status-nya masih belum 1
        $locsToUpdate = Loc::whereIn('no_raf', $soNumbers)
            ->where(function ($q) {
                $q->whereNull('invoice_status')
                  ->orWhere('invoice_status', 0)
                  ->orWhere('invoice_status', false);
            })
            ->get();

        $totalFound = $locsToUpdate->count();
        if ($totalFound === 0) {
            $this->info('Semua data LOC yang memiliki invoice sudah berstatus invoice_status = 1. Tidak ada data yang perlu di-backfill.');
            return 0;
        }

        $this->info("Menemukan {$totalFound} data LOC yang perlu di-update ke invoice_status = 1.");

        $updatedCount = 0;
        $now = now();

        foreach ($locsToUpdate as $loc) {
            DB::transaction(function () use ($loc, $now, &$updatedCount) {
                $loc->update([
                    'invoice_status'     => 1,
                    'invoice_done_count' => DB::raw('COALESCE(invoice_done_count, 0) + 1'),
                    'invoice_done_at'    => $now,
                    'invoice_done_by'    => 'System (Backfill Goodwill)',
                ]);

                InvoiceLog::create([
                    'document_type' => 'loc',
                    'document_id'   => $loc->id,
                    'no_raf'        => $loc->no_raf,
                    'status'        => 'done',
                    'user_id'       => null,
                    'user_name'     => 'System (Backfill Goodwill)',
                    'action_at'     => $now,
                ]);

                $updatedCount++;
            });

            $this->line("-> Updated LOC #{$loc->id} [{$loc->no_raf}] => invoice_status = 1");
        }

        $this->info("Berhasil melakukan backfill pada {$updatedCount} data LOC!");
        return 0;
    }
}

