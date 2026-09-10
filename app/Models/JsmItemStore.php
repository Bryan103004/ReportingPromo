<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JsmItemStore extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(JsmItem::class, 'jsm_item_id');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}
