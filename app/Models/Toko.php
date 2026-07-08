<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function region(){
        return $this->belongsTo(Region::class);
    }

    public function rafaksis()
    {
        return $this->belongsToMany(Rafaksi::class, 'rafaksi_toko', 'toko_id', 'rafaksi_id');
    }

    public function jsm()
    {
        return $this->belongsToMany(Jsm::class, 'jsm_toko', 'toko_id', 'jsm_id');
    }
}
