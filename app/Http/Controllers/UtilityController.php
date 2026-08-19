<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rafaksi;
use App\Models\Jsm;
use App\Models\Pwp;
use Carbon\Carbon;

class UtilityController extends Controller
{
    public function nextNoRaf(Request $request)
    {
        $category = strtoupper($request->query('category', 'RAF'));
        $periode = $request->query('periode');
        $date = $periode ? Carbon::parse($periode) : Carbon::now();
        $month = $date->format('m');
        $year = $date->format('Y');

        if ($category === 'JSM') {
            $maxSeq = Jsm::where('no_raf', 'like', "%/{$year}")->max('raf_sequence');
            $prefix = 'RAFJSM';
        } elseif ($category === 'PWP') {
            $maxSeq = Pwp::where('no_raf', 'like', "%/{$year}")->max('raf_sequence');
            $prefix = 'RAFPWP';
        } else {
            $maxSeq = Rafaksi::where('no_raf', 'like', "%/{$year}")->max('raf_sequence');
            $prefix = 'RAF';
        }

        $nextSeq = $maxSeq ? $maxSeq + 1 : 1;
        $padded = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'no_raf' => "{$prefix}/NF/{$padded}/{$month}/{$year}",
            'sequence' => $nextSeq,
        ]);
    }
}
