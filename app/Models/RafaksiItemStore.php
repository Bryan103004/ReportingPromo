<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RafaksiItemStore extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(RafaksiItem::class, 'rafaksi_item_id');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}
