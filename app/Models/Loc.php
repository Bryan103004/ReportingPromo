<?php

namespace App\Models;

use App\Support\HasDaftarTokoFormatted;
use App\Support\ScopeClaimByFilters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loc extends Model
{
    use HasFactory, HasDaftarTokoFormatted, ScopeClaimByFilters;

    protected $guarded = ['id'];

    protected $table = 'locs';

    protected $casts = [
        'invoice_status' => 'boolean',
    ];

    public function invoiceLogs()
    {
        return $this->hasMany(InvoiceLog::class, 'document_id')->where('document_type', 'loc')->orderBy('action_at', 'desc');
    }

    public function supplierRafaksi()
    {
        return $this->belongsTo(SupplierRafaksi::class, 'supplier_code', 'kode_supplier');
    }

    public function tokos()
    {
        return $this->belongsToMany(Toko::class, 'locs_toko', 'loc_id', 'toko_id');
    }

    public function documents()
    {
        return $this->hasMany(LocDocument::class);
    }



    public function categories(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function reminder(){
        return $this->belongsTo(MasterNotifikasiReminder::class, 'reminder_id', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function invoiceGoodwills()
    {
        return $this->hasMany(InvoiceGoodwill::class, 'so_no', 'no_raf');
    }

    public function latestInvoiceGoodwill()
    {
        return $this->hasOne(InvoiceGoodwill::class, 'so_no', 'no_raf')->latestOfMany();
    }

    public function paymentGoodwills()
    {
        return $this->hasMany(PaymentGoodwill::class, 'so_no', 'no_raf');
    }
}
