<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Loc;
use App\Models\Toko;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TestLocStamp extends Command
{
    protected $signature = 'test:loc-stamp';
    protected $description = 'Create test user, signature, LOC and run approve to test stamping';

    public function handle()
    {
        $this->info('Creating signature PDF...');
        $htmlSig = '<div style="font-size:48px;font-family:sans-serif;color:#000;">Tanda Tangan Demo</div>';
        $pdf = Pdf::loadHTML($htmlSig)->setPaper('A4','portrait');
        Storage::disk('public')->put('signatures/testsig.pdf', $pdf->output());

        $this->info('Creating test user...');
        $user = User::create([
            'name' => 'Test Sign',
            'email' => 'test-sign'.time().'@example.com',
            'password' => Hash::make('password'),
            'signature_path' => 'signatures/testsig.pdf'
        ]);

        $this->info("User created: {$user->id}");
        $this->info('Schema has users.signature_path: ' . (
            \Illuminate\Support\Facades\Schema::hasColumn('users', 'signature_path') ? 'yes' : 'no'
        ));
        $this->info('Created user->signature_path attribute (raw): ' . ($user->signature_path ?? 'NULL'));
        $this->info('Storage signature exists: ' . (Storage::disk('public')->exists('signatures/testsig.pdf') ? 'yes' : 'no'));

        $this->info('Creating document PDF...');
        $htmlDoc = '<h1>Test LOC Document</h1><p>Isi dokumen untuk testing stamping.</p>';
        $pdf2 = Pdf::loadHTML($htmlDoc)->setPaper('A4','portrait');
        Storage::disk('public')->put('loc_documents/orig_loc_test.pdf', $pdf2->output());

        $this->info('Ensuring region and toko...');
        $region = Region::first() ?? Region::create(['nama_region' => 'Test Region', 'status' => 'aktif']);
        $toko = Toko::first() ?? Toko::create(['region_id' => $region->id, 'nama_toko' => 'Test Toko']);

        $this->info('Creating LOC...');
        $loc = Loc::create([
            'supplier_code' => 'TST',
            'supplier_name' => 'Test Supplier',
            'periode_awal' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'periode_akhir' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'periode_bulan' => Carbon::now()->format('Y-m-d'),
            'no_raf' => 'RAF/9999/01/2026',
            'status_email' => 'aktif',
            'store' => 'Test Store',
            'nominal' => 1000,
            'remarks' => 'test',
            'document_path' => 'loc_documents/orig_loc_test.pdf',
            'document_original_name' => 'orig_loc_test.pdf'
        ]);
        $loc->tokos()->sync([$toko->id]);

        $this->info("LOC created: {$loc->id}");

        $this->info('Checking FPDI and file paths before approve...');
        $origPath = Storage::disk('public')->path($loc->document_path);
        $sigPath = Storage::disk('public')->path($user->signature_path);
        $this->info('FPDI class_exists: ' . (class_exists('\\setasign\\Fpdi\\Fpdi') ? 'yes' : 'no'));
        $this->info('Orig path: ' . $origPath . ' exists: ' . (file_exists($origPath) ? 'yes' : 'no'));
        $this->info('Sig path: ' . $sigPath . ' exists: ' . (file_exists($sigPath) ? 'yes' : 'no'));

        $this->info('Logging in as test user and approving LOC...');
        Auth::loginUsingId($user->id);
        try {
            app(\App\Http\Controllers\LocController::class)->approve(new \Illuminate\Http\Request(), $loc);
            $this->info('approve() returned without exception');
        } catch (\Throwable $e) {
            $this->error('approve() threw: ' . $e->getMessage());
        }

        $loc->refresh();
        $this->info('LOC document_path: ' . ($loc->document_path ?? 'NULL'));
        $exists = Storage::disk('public')->exists('loc_documents/stamped_' . $loc->id . '.pdf');
        $this->info('Stamped exists: ' . ($exists ? 'yes' : 'no'));

        return 0;
    }
}
