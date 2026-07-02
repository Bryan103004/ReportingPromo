<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierRafaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function rafaksi()
    {
        return $this->hasMany(Rafaksi::class, 'supplier_code', 'kode_supplier');
    }
}
