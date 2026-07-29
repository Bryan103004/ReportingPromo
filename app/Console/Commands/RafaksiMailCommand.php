<?php

namespace App\Console\Commands;

use App\Mail\RafaksiMail;
use App\Models\Rafaksi;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class RafaksiMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rafaksi-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder Rafaksi';

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

        // 1. Ambil semua data rafaksi OSS yang punya tanggal berakhir
        // Kita filter di level Collection agar lebih fleksibel mengambil data 'bulan'
        $allRafaksi = Rafaksi::with(['supplierRafaksi','tokos', 'categories', 'reminder'])
                    ->whereNotNull('periode_akhir')
                    ->get();

        // 2. Filter data yang benar-benar masuk masa tenggang
        $rafaksiToRemind = $allRafaksi->filter(function ($rafaksi) use ($today) {
            $bulanReminder = $rafaksi->reminder->bulan ?? 0;
            if ($bulanReminder <= 0) return false;

            $tglMulaiReminder = Carbon::parse($rafaksi->periode_akhir)->subMonths($bulanReminder);
            
            // Return true jika: hari ini >= tanggal mulai reminder DAN hari ini <= tanggal berakhir + 1 bulan (toleransi)
            return $today->greaterThanOrEqualTo($tglMulaiReminder) 
                && $today->lessThanOrEqualTo(Carbon::parse($rafaksi->tanggal_berakhir)->addMonth());
        });

        if ($rafaksiToRemind->isEmpty()) {
            $this->info('Tidak ada rafaksi OSS yang memasuki periode notifikasi hari ini.');
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
            Mail::to($user->email)->send(new RafaksiMail($user, $rafaksiToRemind));
            $this->info("Email Reminder Rafaksi dikirim ke: {$user->email}");
        }

        return 0;
    }
}
