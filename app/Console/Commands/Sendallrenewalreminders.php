<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

// Models
use App\Models\Rafaksi;
use App\Models\Loc;
use App\Models\Jsm;
use App\Models\User;
use App\Models\NotificationRecipient;

// Mailable Normal (Due)
use App\Mail\RafaksiMail;
use App\Mail\LocMail;
use App\Mail\JsmMail;

// Mailable Expired (Sudah Lewat Batas)
use App\Mail\ExpiredRafaksiMail;
use App\Mail\ExpiredLocMail;
use App\Mail\ExpiredJsmMail;

class SendAllRenewalReminders extends Command
{
    protected $signature = 'reminder:send-all
                            {--module= : Jalankan modul tertentu saja: rafaksi|loc|jsm}';

    protected $description = 'Kirim semua email reminder renewal (Normal & Expired) ke user internal dan eksternal.';

    private function moduleConfig()
    {
        return [
            'rafaksi' => [
                'label'      => 'Rafaksi',
                'permission' => 'view_rafaksi',
                'handler'    => 'handleRafaksi',
            ],
            'loc' => [
                'label'      => 'Loc',
                'permission' => 'view_loc',
                'handler'    => 'handleLoc',
            ],
            'jsm' => [
                'label'      => 'Jsm',
                'permission' => 'view_jsm',
                'handler'    => 'handleJsm',
            ],
        ];
    }

    public function handle()
    {
        $onlyModule = $this->option('module');

        foreach ($this->moduleConfig() as $key => $config) {
            if ($onlyModule && $onlyModule !== $key) {
                continue;
            }

            $this->info("=== Memproses modul: {$config['label']} ===");
            $this->{$config['handler']}($config['permission'], $key);
            $this->newLine();
        }

        $this->info('Selesai.');
        return 0;
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
    // MODUL: RAFAKSI
    // =========================================================================

    private function handleRafaksi($permission, $moduleKey)
    {
        $todayStr = now()->format('Y-m-d');
        $todayCarbon = Carbon::today();

        // 1. Ambil data dengan eager loading relasi 'tokos'
        $rafaksis = Rafaksi::with('tokos')
            ->where('status_email', '!=', 'tidak_aktif')
            ->whereNotNull('periode_akhir')
            ->whereRaw(
                '? >= DATE_SUB(periode_akhir, INTERVAL reminder_id MONTH)',
                [$todayStr]
            )
            ->get();

        if ($rafaksis->isEmpty()) {
            $this->info('Tidak ada kendaraan Rafaksi yang memasuki periode notifikasi hari ini.');
            return;
        }

        // 2. Gunakan flatMap untuk memetakan ulang data agar bisa di-groupBy berdasarkan toko_id dari pivot
        $groupedRafaksis = $rafaksis->flatMap(function ($rafaksi) {
            return $rafaksi->tokos->map(function ($toko) use ($rafaksi) {
                return [
                    'toko_id' => $toko->id,
                    'toko_obj' => $toko, 
                    'rafaksi' => $rafaksi
                ];
            });
        })->groupBy('toko_id');

        // 3. Loop hasil pengelompokan
        foreach ($groupedRafaksis as $tokoId => $items) {
            
            $toko = $items->first()['toko_obj']; 
            if (! $toko) continue;

            $tokoRafaksis = $items->pluck('rafaksi');

            $users = $this->getUsersByPermissionAndRelation($permission, 'tokos', 'user_toko_accesses', $tokoId);
            $externals = $this->getExternalRecipientsByModule($moduleKey);
            $allRecipients = $this->mergeRecipients($users, $externals);

            if ($allRecipients->isEmpty()) continue;

            foreach ($allRecipients as $recipient) {
                // --- VALIDASI KETAT AKSES TOKO ---
                // Jika penerima adalah User Internal, pastikan dia benar-benar punya akses ke $tokoId ini
                if ($recipient instanceof User) {
                    $hasAccess = $recipient->tokos()->where('tokos.id', $tokoId)->exists();
                    if (!$hasAccess) {
                        continue; // Lewati jika user internal tidak punya akses ke toko ini
                    }
                }

                // Jika penerima adalah Eksternal / memiliki pengaturan filter khusus
                if (!empty($recipient->filters['rafaksi'])) {
                    $f = $recipient->filters['rafaksi'];
                    if (!empty($f['toko_id']) && !in_array($tokoId, $f['toko_id'])) {
                        continue; // Lewati jika toko tidak sesuai dengan filter eksternal
                    }
                }
                // ---------------------------------

                $currentRafaksis = $tokoRafaksis;
                if ($currentRafaksis->isEmpty()) continue; 

                // SPLIT: Due vs Expired
                $dueRafaksis = $currentRafaksis->filter(function($r) use ($todayCarbon) {
                    return !is_null($r->periode_akhir) && 
                        Carbon::parse($r->periode_akhir)->startOfDay()->greaterThanOrEqualTo($todayCarbon);
                });

                // Saring yang Expired (jika periode_akhirnya kosong/null ATAU tanggalnya sudah lewat)
                $expiredRafaksis = $currentRafaksis->filter(function($r) use ($todayCarbon) {
                    // Jika periode_akhir kosong ATAU tanggalnya sudah kurang dari hari ini
                    return !is_null($r->periode_akhir) && 
                        Carbon::parse($r->periode_akhir)->startOfDay()->lessThan($todayCarbon);
                });

                if ($dueRafaksis->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new RafaksiMail($dueRafaksis, $recipient));
                    $this->info("  Email Rafaksi Due → {$recipient->email}");
                }
                
                if ($expiredRafaksis->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new ExpiredRafaksiMail($expiredRafaksis, $recipient));
                    $this->info("  Email Rafaksi Expired → {$recipient->email}");
                }
            }
        }
    }


    // =========================================================================
    // MODUL: LOC
    // =========================================================================

    private function handleLoc($permission, $moduleKey)
    {
        $todayStr = now()->format('Y-m-d');
        $todayCarbon = Carbon::today();

        // 1. Ambil data dengan eager loading relasi 'tokos'
        $locs = Loc::with('tokos')
            ->where('status_email', '!=', 'tidak_aktif')
            ->whereNotNull('periode_akhir')
            ->whereRaw(
                '? >= DATE_SUB(periode_akhir, INTERVAL reminder_id MONTH)',
                [$todayStr]
            )
            ->get();

        if ($locs->isEmpty()) {
            $this->info('Tidak ada Loc yang memasuki periode notifikasi hari ini.');
            return;
        }

        // 2. Gunakan flatMap untuk memetakan ulang data agar bisa di-groupBy berdasarkan toko_id dari pivot
        $groupedLocs = $locs->flatMap(function ($loc) {
            return $loc->tokos->map(function ($toko) use ($loc) {
                return [
                    'toko_id' => $toko->id,
                    'toko_obj' => $toko, 
                    'loc' => $loc
                ];
            });
        })->groupBy('toko_id');

        // 3. Loop hasil pengelompokan
        foreach ($groupedLocs as $tokoId => $items) {
            
            $toko = $items->first()['toko_obj']; 
            if (! $toko) continue;

            $tokoLocs = $items->pluck('loc');

            $users = $this->getUsersByPermissionAndRelation($permission, 'tokos', 'user_toko_accesses', $tokoId);
            $externals = $this->getExternalRecipientsByModule($moduleKey);
            $allRecipients = $this->mergeRecipients($users, $externals);

            if ($allRecipients->isEmpty()) continue;

            foreach ($allRecipients as $recipient) {
                // --- VALIDASI KETAT AKSES TOKO ---
                // Jika penerima adalah User Internal, pastikan dia benar-benar punya akses ke $tokoId ini
                if ($recipient instanceof \App\Models\User) {
                    $hasAccess = $recipient->tokos()->where('tokos.id', $tokoId)->exists();
                    if (!$hasAccess) {
                        continue; // Lewati jika user internal tidak punya akses ke toko ini
                    }
                }

                // Jika penerima adalah Eksternal / memiliki pengaturan filter khusus
                if (!empty($recipient->filters['loc'])) {
                    $f = $recipient->filters['loc'];
                    if (!empty($f['toko_id']) && !in_array($tokoId, $f['toko_id'])) {
                        continue; // Lewati jika toko tidak sesuai dengan filter eksternal
                    }
                }
                // ---------------------------------

                $currentLocs = $tokoLocs;
                if ($currentLocs->isEmpty()) continue; 

                // SPLIT: Due vs Expired
                $dueLocs = $currentLocs->filter(function($l) use ($todayCarbon) {
                    return !is_null($l->periode_akhir) && 
                        Carbon::parse($l->periode_akhir)->startOfDay()->greaterThanOrEqualTo($todayCarbon);
                });

                // Saring yang Expired (jika periode_akhirnya kosong/null ATAU tanggalnya sudah lewat)
                $expiredLocs = $currentLocs->filter(function($l) use ($todayCarbon) {
                    // Jika periode_akhir kosong ATAU tanggalnya sudah kurang dari hari ini
                    return !is_null($l->periode_akhir) && 
                        Carbon::parse($l->periode_akhir)->startOfDay()->lessThan($todayCarbon);
                });

                if ($dueLocs->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new LocMail($recipient, $dueLocs));
                    $this->info("  Email Loc Due → {$recipient->email}");
                }
                
                if ($expiredLocs->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new ExpiredLocMail($recipient, $expiredLocs));
                    $this->info("  Email Loc Expired → {$recipient->email}");
                }
            }
        }
    }

    // =========================================================================
    // MODUL: JSM
    // =========================================================================

    private function handleJsm($permission, $moduleKey)
    {
        $todayStr = now()->format('Y-m-d');
        $todayCarbon = Carbon::today();

        // 1. Ambil data dengan eager loading relasi 'tokos'
        $jsms = Jsm::with('tokos')
            ->where('status_email', '!=', 'tidak_aktif')
            ->whereNotNull('periode_akhir')
            ->whereRaw(
                '? >= DATE_SUB(periode_akhir, INTERVAL reminder_id MONTH)',
                [$todayStr]
            )
            ->get();

        if ($jsms->isEmpty()) {
            $this->info('Tidak ada Jsm yang memasuki periode notifikasi hari ini.');
            return;
        }

        // 2. Gunakan flatMap untuk memetakan ulang data agar bisa di-groupBy berdasarkan toko_id dari pivot
        $groupedJsms = $jsms->flatMap(function ($jsm) {
            return $jsm->tokos->map(function ($toko) use ($jsm) {
                return [
                    'toko_id' => $toko->id,
                    'toko_obj' => $toko, 
                    'jsm' => $jsm
                ];
            });
        })->groupBy('toko_id');

        // 3. Loop hasil pengelompokan
        foreach ($groupedJsms as $tokoId => $items) {
            
            $toko = $items->first()['toko_obj']; 
            if (! $toko) continue;

            $tokoJsms = $items->pluck('jsm');

            $users = $this->getUsersByPermissionAndRelation($permission, 'tokos', 'user_toko_accesses', $tokoId);
            $externals = $this->getExternalRecipientsByModule($moduleKey);
            $allRecipients = $this->mergeRecipients($users, $externals);

            if ($allRecipients->isEmpty()) continue;

            foreach ($allRecipients as $recipient) {
                // --- VALIDASI KETAT AKSES TOKO ---
                // Jika penerima adalah User Internal, pastikan dia benar-benar punya akses ke $tokoId ini
                if ($recipient instanceof \App\Models\User) {
                    $hasAccess = $recipient->tokos()->where('tokos.id', $tokoId)->exists();
                    if (!$hasAccess) {
                        continue; // Lewati jika user internal tidak punya akses ke toko ini
                    }
                }

                // Jika penerima adalah Eksternal / memiliki pengaturan filter khusus
                if (!empty($recipient->filters['jsm'])) {
                    $f = $recipient->filters['jsm'];
                    if (!empty($f['toko_id']) && !in_array($tokoId, $f['toko_id'])) {
                        continue; // Lewati jika toko tidak sesuai dengan filter eksternal
                    }
                }
                // ---------------------------------

                $currentJsms = $tokoJsms;
                if ($currentJsms->isEmpty()) continue; 

                // SPLIT: Due vs Expired
                $dueJsms = $currentJsms->filter(function($j) use ($todayCarbon) {
                    return !is_null($j->periode_akhir) && 
                        Carbon::parse($j->periode_akhir)->startOfDay()->greaterThanOrEqualTo($todayCarbon);
                });

                // Saring yang Expired (jika periode_akhirnya kosong/null ATAU tanggalnya sudah lewat)
                $expiredJsms = $currentJsms->filter(function($j) use ($todayCarbon) {
                    // Jika periode_akhir kosong ATAU tanggalnya sudah kurang dari hari ini
                    return !is_null($j->periode_akhir) && 
                        Carbon::parse($j->periode_akhir)->startOfDay()->lessThan($todayCarbon);
                });

                if ($dueJsms->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new JsmMail($recipient, $dueJsms));
                    $this->info("  Email Jsm Due → {$recipient->email}");
                }
                
                if ($expiredJsms->isNotEmpty()) {
                    Mail::to($recipient->email)->send(new ExpiredJsmMail($recipient, $expiredJsms));
                    $this->info("  Email Jsm Expired → {$recipient->email}");
                }
            }
        }
    }

}