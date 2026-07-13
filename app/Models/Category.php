<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rafaksis(){
        return $this->hasMany(Rafaksi::class);
    }

    public function jsms(){
        return $this->hasMany(Jsm::class);
    }
}
