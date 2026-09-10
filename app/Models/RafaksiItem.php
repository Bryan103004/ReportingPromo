<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RafaksiItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rafaksi()
    {
        return $this->belongsTo(Rafaksi::class);
    }

    public function stores()
    {
        return $this->hasMany(RafaksiItemStore::class);
    }
    
}
