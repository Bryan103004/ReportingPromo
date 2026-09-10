<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function loc()
    {
        return $this->belongsTo(Loc::class);
    }
}
