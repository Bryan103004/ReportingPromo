<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JsmItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function jsm()
    {
        return $this->belongsTo(Jsm::class);
    }

    public function stores()
    {
        return $this->hasMany(JsmItemStore::class);
    }
}
