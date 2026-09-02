<?php

namespace App\Console\Commands;

use App\Models\Jsm;
use App\Models\Pwp;
use App\Models\Rafaksi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $table = (new $modelClass())->getTable();
        $rows = $modelClass::whereNull('raf_sequence')->whereNotNull('no_raf')->get(['id', 'no_raf']);

        if ($rows->isEmpty()) {
            $this->info("{$label}: nothing to backfill.");
            return;
        }

        // id => parsed sequence, for every row we can confidently parse
        $toUpdate = [];
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

            $toUpdate[$row->id] = (int) $seqPart;
        }

        $verb = $dryRun ? 'would update' : 'updated';

        if ($dryRun) {
            $this->info("{$label}: {$verb} " . count($toUpdate) . " row(s), skipped {$skipped} (couldn't parse no_raf).");
            return;
        }

        // Batch into single CASE-WHEN UPDATE statements instead of one query per row —
        // saving 25k+ rows one at a time (Eloquent ->save() per row) is what made this
        // command look "frozen" for minutes: it prints nothing until the whole loop ends.
        $bar = $this->output->createProgressBar(count($toUpdate));
        $bar->start();

        foreach (array_chunk($toUpdate, 500, true) as $chunk) {
            $cases = [];
            $ids = [];
            foreach ($chunk as $id => $sequence) {
                $id = (int) $id;
                $sequence = (int) $sequence;
                $cases[] = "WHEN {$id} THEN {$sequence}";
                $ids[] = $id;
            }

            DB::statement(
                "UPDATE `{$table}` SET raf_sequence = CASE id " . implode(' ', $cases) . " END WHERE id IN (" . implode(',', $ids) . ")"
            );

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$label}: {$verb} " . count($toUpdate) . " row(s), skipped {$skipped} (couldn't parse no_raf).");
    }
}
