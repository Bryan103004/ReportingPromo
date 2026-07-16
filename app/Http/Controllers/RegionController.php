<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    //
    public function index(){
        $regions = Region::customPaginate();
        return view('region.index', compact('regions'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'kode_region' => 'required|string',
            'nama_region' => 'required|string'
        ]);

        $regionPayload = collect($validatedData)->all();
        $region  = Region::create($regionPayload);

        ActivityLogger::logCreate(
            $region,
            $region->id,
            $request->only(['kode_region', 'nama_region']),
            "Created Region #{$region->id}: {$region->nama_region}"
        );

        return redirect()->back()->with('success', "Region berhasi di tambahkan!");
    }

    public function edit(Region $region){
        return view('region.edit', compact('region'));
    }

    public function update(Request $request, Region $region){
        $validatedData = $request->validate([
            'kode_region' => 'required|string',
            'nama_region' => 'required|string'
        ]);


        $region->update($validatedData);
        
        ActivityLogger::logUpdate(
            $region,
            $region->id,
            $request->only(['kode_region', 'nama_region']),
            "Created Region #{$region->id}: {$region->nama_region}"
        );

        return redirect()->route('region.index')->with('success', "Region berhasi di update!");
    }

    public function destroy(Region $region){
        $region->delete();

        ActivityLogger::logUpdate(
            $region,
            $region->id,
            ['kode_region' => $region->kode_region, 'nama_region' => $region->nama_region],
            "Deleted Region #{$region->id}: {$region->nama_region}"
        );

        return redirect()->back()->with('success','Region berhasil di delete');
    }
}
