<?php

namespace App\Models;

use App\Support\HasDaftarTokoFormatted;
use App\Support\ScopeClaimByFilters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jsm extends Model
{
    use HasFactory, HasDaftarTokoFormatted, ScopeClaimByFilters;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_status' => 'boolean',
    ];

    public function invoiceLogs()
    {
        return $this->hasMany(InvoiceLog::class, 'document_id')->where('document_type', 'jsm')->orderBy('action_at', 'desc');
    }


    protected $table = 'jsm';

    public function getNominalFormattedAttribute()
    {
        $nominal_formatted = $this->nominal ?? 0; 
        
        return number_format($nominal_formatted, 0, ',', '.');
    }
    

    public function supplierRafaksi()
    {
        return $this->belongsTo(SupplierRafaksi::class, 'supplier_code', 'kode_supplier');
    }

    public function tokos()
    {
        return $this->belongsToMany(Toko::class, 'jsm_toko', 'jsm_id', 'toko_id');
    }

    public function items()
    {
        return $this->hasMany(JsmItem::class);
    }

    public function documents()
    {
        return $this->hasMany(JsmDocument::class);
    }



    public function categories(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function reminder(){
        return $this->belongsTo(MasterNotifikasiReminder::class, 'reminder_id', 'id');
    }
}
