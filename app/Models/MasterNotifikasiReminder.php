<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterNotifikasiReminder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    // Relasi ke Master Perizinan
    public function rafaksis()
    {
        return $this->hasMany(Rafaksi::class, 'reminder_id');
    }

    public function jsms()
    {
        return $this->hasMany(Jsm::class, 'reminder_id');
    }

    public function locs(){
        return $this->hasMany(Loc::class, 'reminder_id');
    }

    public function inbounds(){
        return $this->hasMany(Inbound::class, 'reminder_id');
    }
}
