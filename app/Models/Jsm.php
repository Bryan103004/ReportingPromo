<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jsm extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'jsm';

    public function supplierRafaksi()
    {
        return $this->belongsTo(SupplierRafaksi::class, 'supplier_code', 'kode_supplier');
    }

    public function tokos()
    {
        return $this->belongsToMany(Toko::class, 'jsm_toko', 'jsm_id', 'toko_id');
    }

    public function getDaftarTokoFormattedAttribute()
    {
        // Jika tidak ada toko yang berelasi, kembalikan strip
        if ($this->tokos->isEmpty()) {
            return '-';
        }

        // Ambil region_id dari toko pertama yang diceklis
        $regionId = $this->tokos->first()->region_id;

        // Hitung total seluruh toko di database yang berada di region tersebut
        $totalTokoDiRegion = Toko::where('region_id', $regionId)->count();

        // Jika jumlah toko yang berelasi SAMA DENGAN total toko di region itu
        if ($this->tokos->count() === $totalTokoDiRegion) {
            // Tampilkan nama Region (karena kamu menyimpan nama region di kolom 'store')
            return $this->store; 
        }

        // Jika hanya sebagian (tidak lengkap), kembalikan daftar nama toko pakai koma
        return $this->tokos->pluck('nama_toko')->implode(', ');
    }

    public function categories(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
