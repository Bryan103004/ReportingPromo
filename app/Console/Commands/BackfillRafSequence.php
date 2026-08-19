<?php

namespace App\Console\Commands;

use App\Models\Jsm;
use App\Models\Pwp;
use App\Models\Rafaksi;
use Illuminate\Console\Command;

class BackfillRafSequence extends Command
{
    /**
     * php artisan raf:backfill-sequence          -> writes raf_sequence for legacy rows
     * php artisan raf:backfill-sequence --dry-run -> only reports what would change
     */
    protected $signature = 'raf:backfill-sequence {--dry-run}';
    protected $description = 'Backfill raf_sequence on rafaksis/jsm/pwps rows that predate the raf_sequence column, by parsing it out of no_raf';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        foreach ([Rafaksi::class, Jsm::class, Pwp::class] as $modelClass) {
            $this->backfillModel($modelClass, $dryRun);
        }

        return 0;
    }

    protected function backfillModel(string $modelClass, bool $dryRun): void
    {
        $label = class_basename($modelClass);
        $rows = $modelClass::whereNull('raf_sequence')->whereNotNull('no_raf')->get();

        if ($rows->isEmpty()) {
            $this->info("{$label}: nothing to backfill.");
            return;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // no_raf is always "<...prefix segments...>/<sequence>/<month>/<year>",
            // regardless of how many prefix segments the format has (old: RAF/0001/08/2026,
            // new: RAF/NF/0001/08/2026) — so the sequence is always 3rd from the end.
            $parts = explode('/', $row->no_raf);

            if (count($parts) < 3) {
                $skipped++;
                continue;
            }

            $seqPart = $parts[count($parts) - 3];

            if (! ctype_digit($seqPart)) {
                $skipped++;
                continue;
            }

            $sequence = (int) $seqPart;

            if (! $dryRun) {
                $row->raf_sequence = $sequence;
                $row->save();
            }

            $updated++;
        }

        $verb = $dryRun ? 'would update' : 'updated';
        $this->info("{$label}: {$verb} {$updated} row(s), skipped {$skipped} (couldn't parse no_raf).");
    }
}
