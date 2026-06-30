<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {   
        $number_paginate = [10, 25, 50, 100, 300, 999999999];
        $number = $request->input('number', 10);

        $query = Activity::orderBy('created_at', 'desc');

        $logs = $query->paginate($number);
        return view('activity_log.index', compact('logs','number', 'number_paginate'));
    }

    public function destroy()
    {
        // 1. Ambil nama user yang sedang login
        $user = auth()->user()->name;

        // 2. Hapus semua data log langsung dari database
        Activity::query()->delete();

        $logs = Activity::all();
        $aksi = " | Clear Semua Log";
        // 3. Catat aktivitas penghapusan ke dalam logger
        ActivityLogger::logDelete($aksi, $aksi, "Logs berhasil di DELETE oleh: {$user}.");

        return redirect()->route('activity-log.index')->with('success', 'Log berhasil di bersihkan.');
    }

}