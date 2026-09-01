<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

// Models
use App\Models\Rafaksi;
use App\Models\Loc;
use App\Models\Jsm;
use App\Models\Pwp;
use App\Models\User;
use App\Models\NotificationRecipient;

// Mailable Normal (Due)
use App\Mail\RafaksiMail;
use App\Mail\LocMail;
use App\Mail\JsmMail;
use App\Mail\PwpMail;

// Mailable Expired (Sudah Lewat Batas)
use App\Mail\ExpiredRafaksiMail;
use App\Mail\ExpiredLocMail;
use App\Mail\ExpiredJsmMail;
use App\Mail\ExpiredPwpMail;

class SendAllRenewalReminders extends Command
{
    protected $signature = 'reminder:send-all
                            {--module= : Jalankan modul tertentu saja: rafaksi|loc|jsm|pwp}';

    protected $description = 'Kirim semua email reminder renewal (Normal & Expired) ke user internal dan eksternal.';

    /**
     * Path file log (public/logs/log.txt), diinisialisasi sekali di awal handle().
     */
    private ?string $logPath = null;

    private function moduleConfig()
    {
        return [
            'rafaksi' => [
                'label'        => 'Rafaksi',
                'permission'   => 'view_rafaksi',
                'model'        => Rafaksi::class,
                'mail'         => RafaksiMail::class,
                'expiredMail'  => ExpiredRafaksiMail::class,
            ],
            'loc' => [
                'label'        => 'Loc',
                'permission'   => 'view_loc',
                'model'        => Loc::class,
                'mail'         => LocMail::class,
                'expiredMail'  => ExpiredLocMail::class,
            ],
            'jsm' => [
                'label'        => 'Jsm',
                'permission'   => 'view_jsm',
                'model'        => Jsm::class,
                'mail'         => JsmMail::class,
                'expiredMail'  => ExpiredJsmMail::class,
            ],
            'pwp' => [
                'label'        => 'Pwp',
                'permission'   => 'view_pwp',
                'model'        => Pwp::class,
                'mail'         => PwpMail::class,
                'expiredMail'  => ExpiredPwpMail::class,
            ],
        ];
    }

    public function handle()
    {
        $this->initLog();
        $this->log('==================== MULAI reminder:send-all ====================');

        $onlyModule = $this->option('module');

        foreach ($this->moduleConfig() as $key => $config) {
            if ($onlyModule && $onlyModule !== $key) {
                continue;
            }

            $this->info("=== Memproses modul: {$config['label']} ===");
            $this->log("[{$config['label']}] Mulai diproses.");

            try {
                $this->processModule($config, $key);
            } catch (\Throwable $e) {
                // Kalau ada error tak terduga di level modul, jangan sampai modul lain ikut gagal.
                $this->error("  Modul {$config['label']} gagal diproses: " . $e->getMessage());
                $this->log("[{$config['label']}] ERROR TIDAK TERDUGA di level modul: " . $e->getMessage());
            }

            $this->newLine();
        }

        $this->log('==================== SELESAI reminder:send-all ====================');
        $this->info('Selesai.');
        return 0;
    }

    // =========================================================================
    // LOGGING — public/logs/log.txt
    // =========================================================================

    private function initLog(): void
    {
        $dir = public_path('logs');
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->logPath = $dir . '/log.txt';
    }

    private function log(string $message): void
    {
        if (! $this->logPath) {
            $this->initLog();
        }
        $line = '[' . now()->format('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($this->logPath, $line, FILE_APPEND | LOCK_EX);
    }

    // =========================================================================
    // HELPERS — USER LOGIN & PENERIMA EKSTERNAL
    // =========================================================================

    private function getUsersByPermission($permission)
    {
        return User::whereNotNull('email')->permission($permission)->get();
    }

    private function getUsersByPermissionAndRelation($permission, $relation, $pivotTable, $entityId)
    {
        return User::whereNotNull('email')
            ->permission($permission)
            ->whereHas($relation, function ($q) use ($pivotTable, $entityId) {
                $q->where("{$pivotTable}.toko_id", $entityId);
            })
            ->get();
    }

    private function getExternalRecipientsByModule($moduleKey)
    {
        return NotificationRecipient::active()
            ->forModule($moduleKey)
            ->get()
            ->map(function ($recipient) {
                return (object) [
                    'name'    => $recipient->name,
                    'email'   => $recipient->email,
                    'filters' => $recipient->filters,
                ];
            });
    }

    private function mergeRecipients($users, $externals)
    {
        return $users->concat($externals)->unique('email')->values();
    }

    // =========================================================================
    // PEMROSESAN MODUL (dipakai bareng oleh rafaksi/loc/jsm/pwp)
    // =========================================================================

    private function processModule(array $config, string $moduleKey): void
    {
        $label            = strtoupper($config['label']);
        $modelClass       = $config['model'];
        $mailClass        = $config['mail'];
        $expiredMailClass = $config['expiredMail'];
        $permission       = $config['permission'];

        $todayStr    = now()->format('Y-m-d');
        $todayCarbon = Carbon::today();

        // 1. Ambil data dengan eager loading relasi 'tokos'.
        // IFNULL(reminder_id, 0) supaya baris tanpa reminder_id tidak diam-diam ke-skip dari query.
        $items = $modelClass::with('tokos')
            ->where('status_email', '!=', 'tidak_aktif')
            ->whereNotNull('periode_akhir')
            ->whereRaw(
                '? >= DATE_SUB(periode_akhir, INTERVAL IFNULL(reminder_id, 0) MONTH)',
                [$todayStr]
            )
            ->get();

        if ($items->isEmpty()) {
            $msg = "Tidak ada data {$config['label']} yang memasuki periode notifikasi hari ini.";
            $this->info('  ' . $msg);
            $this->log("[{$label}] {$msg}");
            return;
        }

        $this->log("[{$label}] Ditemukan {$items->count()} data yang masuk periode reminder hari ini.");

        // 2. Kelompokkan berdasarkan toko_id dari pivot
        $grouped = $items->flatMap(function ($item) {
            return $item->tokos->map(function ($toko) use ($item) {
                return [
                    'toko_id'  => $toko->id,
                    'toko_obj' => $toko,
                    'item'     => $item,
                ];
            });
        })->groupBy('toko_id');

        $tokoProcessed         = 0;
        $emailSent             = 0;
        $emailFailed           = 0;
        $tokoSkippedNoRecipient = 0;

        // 3. Loop hasil pengelompokan
        foreach ($grouped as $tokoId => $groupItems) {
            try {
                $toko = $groupItems->first()['toko_obj'];
                if (! $toko) {
                    continue;
                }

                $tokoProcessed++;
                $tokoItems = $groupItems->pluck('item');

                $users         = $this->getUsersByPermissionAndRelation($permission, 'tokos', 'user_toko_accesses', $tokoId);
                $externals     = $this->getExternalRecipientsByModule($moduleKey);
                $allRecipients = $this->mergeRecipients($users, $externals);

                if ($allRecipients->isEmpty()) {
                    $tokoSkippedNoRecipient++;
                    $this->log("[{$label}] Toko '{$toko->nama_toko}' (ID {$tokoId}): {$tokoItems->count()} data — TIDAK ADA PENERIMA (tidak ada user dengan akses toko ini & tidak ada notification recipient eksternal untuk modul {$moduleKey}) — DILEWATI.");
                    continue;
                }

                foreach ($allRecipients as $recipient) {
                    // --- VALIDASI KETAT AKSES TOKO ---
                    if ($recipient instanceof User) {
                        $hasAccess = $recipient->tokos()->where('tokos.id', $tokoId)->exists();
                        if (! $hasAccess) {
                            continue;
                        }
                    }

                    // Filter khusus untuk penerima eksternal
                    if (! empty($recipient->filters[$moduleKey])) {
                        $f = $recipient->filters[$moduleKey];
                        if (! empty($f['toko_id']) && ! in_array($tokoId, $f['toko_id'])) {
                            continue;
                        }
                    }
                    // ---------------------------------

                    if ($tokoItems->isEmpty()) {
                        continue;
                    }

                    // SPLIT: Due vs Expired
                    $due = $tokoItems->filter(function ($r) use ($todayCarbon) {
                        return ! is_null($r->periode_akhir) &&
                            Carbon::parse($r->periode_akhir)->startOfDay()->greaterThanOrEqualTo($todayCarbon);
                    });

                    $expired = $tokoItems->filter(function ($r) use ($todayCarbon) {
                        return ! is_null($r->periode_akhir) &&
                            Carbon::parse($r->periode_akhir)->startOfDay()->lessThan($todayCarbon);
                    });

                    if ($due->isNotEmpty()) {
                        $noRafList = $due->pluck('no_raf')->implode(', ');
                        try {
                            Mail::to($recipient->email)->send(new $mailClass($due, $recipient));
                            $emailSent++;
                            $this->info("  Email {$config['label']} Due → {$recipient->email}");
                            $this->log("[{$label}] TERKIRIM (due) -> {$recipient->email} | Toko: {$toko->nama_toko} | Data: {$noRafList}");
                        } catch (\Throwable $e) {
                            $emailFailed++;
                            $this->error("  Gagal kirim {$config['label']} Due -> {$recipient->email}: " . $e->getMessage());
                            $this->log("[{$label}] GAGAL (due) -> {$recipient->email} | Toko: {$toko->nama_toko} | Data: {$noRafList} | Error: " . $e->getMessage());
                        }
                    }

                    if ($expired->isNotEmpty()) {
                        $noRafList = $expired->pluck('no_raf')->implode(', ');
                        try {
                            Mail::to($recipient->email)->send(new $expiredMailClass($expired, $recipient));
                            $emailSent++;
                            $this->info("  Email {$config['label']} Expired → {$recipient->email}");
                            $this->log("[{$label}] TERKIRIM (expired) -> {$recipient->email} | Toko: {$toko->nama_toko} | Data: {$noRafList}");
                        } catch (\Throwable $e) {
                            $emailFailed++;
                            $this->error("  Gagal kirim {$config['label']} Expired -> {$recipient->email}: " . $e->getMessage());
                            $this->log("[{$label}] GAGAL (expired) -> {$recipient->email} | Toko: {$toko->nama_toko} | Data: {$noRafList} | Error: " . $e->getMessage());
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Kalau ada 1 toko/baris yang errornya di luar dugaan, lewati saja, jangan hentikan modul lain.
                $this->error('  Terjadi error saat memproses salah satu toko, dilewati: ' . $e->getMessage());
                $this->log("[{$label}] ERROR saat memproses toko ID {$tokoId}, DILEWATI. Error: " . $e->getMessage());
                continue;
            }
        }

        $this->log("[{$label}] Selesai. Toko diproses: {$tokoProcessed}, Email terkirim: {$emailSent}, Gagal: {$emailFailed}, Dilewati (tanpa penerima): {$tokoSkippedNoRecipient}.");
    }
}
