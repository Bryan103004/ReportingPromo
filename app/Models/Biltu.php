<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biltu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_mba' => 'boolean',
    ];

    /**
     * Relasi ke SupplierRafaksi berdasarkan vendor_no -> kode_supplier
     */
    public function supplier()
    {
        return $this->belongsTo(SupplierRafaksi::class, 'vendor_no', 'kode_supplier');
    }

    /**
     * Tampilan label di dropdown: 1125516 - C00010 (PT. BAHANA GENTA VIKTORY)
     */
    public function getDropdownLabelAttribute(): string
    {
        $id = $this->C_BPartner_ID ?? '-';
        $val = $this->value ? " - {$this->value}" : '';
        $name = $this->name ? " ({$this->name})" : '';
        return "{$id}{$val}{$name}";
    }
}
