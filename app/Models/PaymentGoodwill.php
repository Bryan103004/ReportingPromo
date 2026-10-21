<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGoodwill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'inv_date'          => 'date',
        'last_updated_date' => 'date',
        'bill_date'         => 'date',
        'allocation_date'   => 'date',
        'payment_date'      => 'date',
        'grand_total'       => 'decimal:2',
        'allocation_amt'    => 'decimal:2',
    ];

    /**
     * Relasi ke data Invoice berdasarkan inv_no
     */
    public function invoice()
    {
        return $this->belongsTo(InvoiceGoodwill::class, 'inv_no', 'inv_no');
    }

    /**
     * Relasi ke data LOC berdasarkan so_no = no_raf
     */
    public function loc()
    {
        return $this->belongsTo(Loc::class, 'so_no', 'no_raf');
    }
}

