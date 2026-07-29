<?php

namespace App\Console\Commands;

use App\Mail\LocMail;
use App\Models\Loc;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class LocMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:loc-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder LOC';

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
        $today = Carbon::today();

        // 1. Ambil semua data loc OSS yang punya tanggal berakhir
        // Kita filter di level Collection agar lebih fleksibel mengambil data 'bulan'
        $allLoc = Loc::with(['supplierRafaksi','tokos', 'categories', 'reminder'])
                    ->whereNotNull('periode_akhir')
                    ->get();

        // 2. Filter data yang benar-benar masuk masa tenggang
        $locToRemind = $allLoc->filter(function ($loc) use ($today) {
            $bulanReminder = $loc->reminder->bulan ?? 0;
            if ($bulanReminder <= 0) return false;

            $tglMulaiReminder = Carbon::parse($loc->periode_akhir)->subMonths($bulanReminder);
            
            // Return true jika: hari ini >= tanggal mulai reminder DAN hari ini <= tanggal berakhir + 1 bulan (toleransi)
            return $today->greaterThanOrEqualTo($tglMulaiReminder) 
                && $today->lessThanOrEqualTo(Carbon::parse($loc->tanggal_berakhir)->addMonth());
        });

        if ($locToRemind->isEmpty()) {
            $this->info('Tidak ada loc OSS yang memasuki periode notifikasi hari ini.');
            return 0;
        }

        // 3. Ambil User tujuan
        $users = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['marketing', 'admin', 'superadmin', 'buyer']); 
                })
                ->whereNotNull('email')
                ->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user dengan role sesuai untuk dikirimi email.');
            return 0;
        }

        // 4. Kirim Email Rekap (Satu email berisi semua izin yang perlu di-reminder)
        foreach ($users as $user) {
            Mail::to($user->email)->send(new LocMail($user, $locToRemind));
            $this->info("Email Reminder Rafaksi dikirim ke: {$user->email}");
        }

        return 0;
    }
}
