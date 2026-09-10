<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwpDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function pwp()
    {
        return $this->belongsTo(Pwp::class);
    }
}
