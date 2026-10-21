<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceGoodwill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'inv_date'          => 'date',
        'last_updated_date' => 'date',
        'bill_date'         => 'date',
        'grand_total'       => 'decimal:2',
    ];

    /**
     * Relasi ke data Payment/Alokasi berdasarkan inv_no
     */
    public function payments()
    {
        return $this->hasMany(PaymentGoodwill::class, 'inv_no', 'inv_no');
    }

    /**
     * Relasi ke data LOC berdasarkan so_no = no_raf
     */
    public function loc()
    {
        return $this->belongsTo(Loc::class, 'so_no', 'no_raf');
    }

    /**
     * Total amount yang sudah teralokasi dari payments
     */
    public function getTotalAllocatedAttribute(): float
    {
        return (float) $this->payments()->sum('allocation_amt');
    }
}

