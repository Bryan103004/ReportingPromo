<?php

namespace App\Http\Controllers;

use App\Models\Jsm;
use App\Models\Loc;
use App\Models\NotificationRecipient;
use App\Models\Rafaksi;
use App\Models\Toko;
use Illuminate\Http\Request;

class NotificationRecipientController extends Controller
{
    const AVAILABLE_MODULES = [
        'rafaksi' => 'Rafaksi',
        'loc'  => 'Loc',
        'jsm' => 'Jsm',
    ];

    // Helper untuk mengambil semua master data yang dibutuhkan filter
    private function getMasterData()
    {
        return [
            'modules'     => self::AVAILABLE_MODULES,
            'tokos'       => Toko::orderBy('nama_toko', 'asc')->get(),
            'rafaksi'     => Rafaksi::orderBy('id', 'asc')->get(),
            'loc'         => Loc::orderBy('id', 'asc')->get(),
            'jsm'         => Jsm::orderBy('id', 'asc')->get(),
        ];
    }

    public function index()
    {
        $recipients = NotificationRecipient::orderBy('name','asc')->paginate(20);
        $modules    = self::AVAILABLE_MODULES;
        return view('master.notification-recipients.index', compact('recipients', 'modules'));
    }

    public function create()
    {
        return view('master.notification-recipients.create', $this->getMasterData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:notification_recipients,email',
            'modules'   => 'required|array|min:1',
            'modules.*' => 'in:' . implode(',', array_keys(self::AVAILABLE_MODULES)),
            'is_active' => 'boolean',
            'filters'   => 'nullable|array', // Validasi filter opsional
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        
        // Bersihkan filter agar hanya menyimpan data modul yang dicentang saja
        $validated['filters'] = $this->cleanFilters($request->input('filters', []), $validated['modules']);

        NotificationRecipient::create($validated);

        return redirect()
            ->route('notification-recipients.index')
            ->with('success', "Penerima {$validated['email']} berhasil ditambahkan.");
    }

    public function edit(NotificationRecipient $notificationRecipient)
    {
        $data = $this->getMasterData();
        $data['notificationRecipient'] = $notificationRecipient;
        return view('master.notification-recipients.edit', $data);
    }

    public function update(Request $request, NotificationRecipient $notificationRecipient)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:notification_recipients,email,' . $notificationRecipient->id,
            'modules'   => 'required|array|min:1',
            'modules.*' => 'in:' . implode(',', array_keys(self::AVAILABLE_MODULES)),
            'is_active' => 'boolean',
            'filters'   => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['filters'] = $this->cleanFilters($request->input('filters', []), $validated['modules']);

        $notificationRecipient->update($validated);

        return redirect()
            ->route('notification-recipients.index')
            ->with('success', "Data penerima {$notificationRecipient->email} berhasil diperbarui.");
    }

    public function destroy(NotificationRecipient $notificationRecipient)
    {
        $email = $notificationRecipient->email;
        $notificationRecipient->delete();
        return redirect()->route('notification-recipients.index')->with('success', "Penerima <strong>{$email}</strong> berhasil dihapus.");
    }


    // Helper menghapus data filter jika modul terkait tidak dicentang oleh admin
    private function cleanFilters($filters, $activeModules)
    {
        $clean = [];
        foreach ($activeModules as $module) {
            if (isset($filters[$module]) && is_array($filters[$module])) {
                foreach ($filters[$module] as $filterKey => $filterValue) {
                    // Jika isinya array (seperti toko_id[]), filter isi di dalamnya agar tidak ada yang kosong/null
                    if (is_array($filterValue)) {
                        $filteredValues = array_filter($filterValue, function($item) {
                            return !is_null($item) && $item !== '';
                        });
                        
                        // Masukkan kembali hanya jika ada data yang dipilih
                        if (!empty($filteredValues)) {
                            // Menggunakan array_values agar index array-nya rapi (0, 1, 2, dst)
                            $clean[$module][$filterKey] = array_values($filteredValues);
                        }
                    } else {
                        // Untuk filter biasa jika bukan array
                        if (!is_null($filterValue) && $filterValue !== '') {
                            $clean[$module][$filterKey] = $filterValue;
                        }
                    }
                }
            }
        }
        return $clean;
    }
}