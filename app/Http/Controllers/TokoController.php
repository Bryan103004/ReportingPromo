<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Toko;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    //
    public function index(){
        $tokos = Toko::customPaginate();
        $regions = Region::all();
        return view('toko.index', compact('tokos','regions'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'region_id' => 'required|numeric',
            'nama_toko' => 'required|string',
            'kode_toko' => 'nullable|string',
            'nama_pt' => 'nullable|string',
            'alamat_pt' => 'nullable|string',
            'npwp' => 'nullable|string',
            'alamat_toko' => 'nullable|string',
            'id_alias' => 'nullable|string'
        ]);

        $tokoPayload = collect($validatedData)->all();
        
        $toko = Toko::create($tokoPayload);

        ActivityLogger::logCreate(
            $toko,
            $toko->id,
            $request->only(['region_id', 'nama_toko']),
            "Created Toko #{$toko->id}: {$toko->nama_toko}"
        );

        return redirect()->back()->with('success', 'Toko berhasil dibuat!');
    }

    public function edit(Toko $toko){
        $regions = Region::all();
        return view('toko.edit', compact('toko','regions'));
    }

    public function update(Request $request, Toko $toko){
        $validatedData = $request->validate([
            'region_id' => 'required|numeric',
            'nama_toko' => 'required|string',
            'kode_toko' => 'nullable|string',
            'nama_pt' => 'nullable|string',
            'alamat_pt' => 'nullable|string',
            'npwp' => 'nullable|string',
            'alamat_toko' => 'nullable|string',
            'id_alias' => 'nullable|string'
        ]);

        $toko->update($validatedData);

        ActivityLogger::logUpdate(
            $toko,
            $toko->id,
            $request->only(['region_id', 'nama_toko']),
            "Updated Toko #{$toko->id}: {$toko->nama_toko}"
        );

        return redirect()->route('toko.index')->with('success', 'Toko berhasil diupdate!');
    }


    public function destroy(Toko $toko){
        $toko->delete();

        ActivityLogger::logDelete(
            $toko,
            $toko->id,
            ['region_id' => $toko->region_id, 'nama_toko' => $toko->nama_toko],
            "Deleted Region #{$toko->id}: {$toko->nama_toko}"
        );

        return redirect()->back()->with('success','Toko berhasil dihapus!');
    }

    public function getByRegion($region_id)
    {
        // Ambil toko yang sesuai dengan region_id
        $query = Toko::whereNotIn('status',['nonaktif'])
                    ->where('region_id', $region_id);

        $this->applyNamePtFilter($query, request());

        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $ids = auth()->user()->accessibleTokoIds()->toArray();
            if (empty($ids)) {
                return response()->json([]);
            }
            $query->whereIn('id', $ids);
        }

        $tokos = $query->get(['id', 'nama_toko']);
        return response()->json($tokos);
    }

    public function getAll(Request $request)
    {
        $query = Toko::whereNotIn('status',['nonaktif']);

        $this->applyNamePtFilter($query, $request);

        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $ids = auth()->user()->accessibleTokoIds()->toArray();
            if (empty($ids)) {
                return response()->json([]);
            }
            $query->whereIn('id', $ids);
        }

        $tokos = $query->get(['id', 'nama_toko']);
        return response()->json($tokos);
    }

    /**
     * Applies the nama_pt filter to a toko query.
     *
     * ?name_pt=PT. MITRA BELANJA ANDA&name_pt_mode=include  -> only that PT (default mode when name_pt is set)
     * ?name_pt=PT. MITRA BELANJA ANDA&name_pt_mode=exclude  -> everything except that PT (nulls included)
     * no name_pt / mode=all                                  -> unfiltered
     */
    protected function applyNamePtFilter($query, Request $request): void
    {
        $namePt = $request->query('name_pt');
        $mode = $request->query('name_pt_mode', $namePt ? 'include' : 'all');

        if ($mode === 'exclude' && $namePt) {
            $query->where(function ($q) use ($namePt) {
                $q->where('nama_pt', '!=', $namePt)->orWhereNull('nama_pt');
            });
        } elseif ($mode === 'include' && $namePt) {
            $query->where('nama_pt', $namePt);
        }
    }
}
