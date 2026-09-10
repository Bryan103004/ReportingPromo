<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwpItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function pwp()
    {
        return $this->belongsTo(Pwp::class);
    }

    public function stores()
    {
        return $this->hasMany(PwpItemStore::class);
    }
}
