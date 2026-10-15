<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategorySignatory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategorySpvController extends Controller
{
    public function __construct()
    {
        // Pengaturan SPV kategori nentuin siapa yang boleh toggle status "Siap Kirim
        // ke AR" dan tampil tanda tangannya di dokumen cetak -- cuma admin/superadmin
        // yang boleh ubah, bukan sembarang user dengan permission can_update biasa.
        $this->middleware(function ($request, $next) {
            if (! auth()->user()?->hasAnyRole(['superadmin', 'admin'])) {
                abort(403, 'Hanya admin/superadmin yang bisa mengatur SPV kategori.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $categories = Category::with(['signatories.user'])->orderBy('nama_kategori')->get();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('category-spv.index', compact('categories', 'users'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'prepared_by'     => 'nullable|array',
            'prepared_by.*'   => 'integer|exists:users,id',
            'acknowledged_by'   => 'nullable|array',
            'acknowledged_by.*' => 'integer|exists:users,id',
        ]);

        $preparedIds = collect($validated['prepared_by'] ?? [])->unique()->values();
        $acknowledgedIds = collect($validated['acknowledged_by'] ?? [])->unique()->values();

        DB::transaction(function () use ($category, $preparedIds, $acknowledgedIds) {
            $category->signatories()->where('role', 'prepared_by')->delete();
            $category->signatories()->where('role', 'acknowledged_by')->delete();

            foreach ($preparedIds as $userId) {
                CategorySignatory::create([
                    'category_id' => $category->id,
                    'role'        => 'prepared_by',
                    'user_id'     => $userId,
                ]);
            }

            foreach ($acknowledgedIds as $userId) {
                CategorySignatory::create([
                    'category_id' => $category->id,
                    'role'        => 'acknowledged_by',
                    'user_id'     => $userId,
                ]);
            }
        });

        return redirect()->route('category-spv.index')
            ->with('success', "SPV untuk kategori {$category->nama_kategori} berhasil diperbarui.");
    }
}
