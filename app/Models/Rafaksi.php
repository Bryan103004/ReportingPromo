<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rafaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'invoice_status' => 'boolean',
    ];

    public function invoiceLogs()
    {
        return $this->hasMany(InvoiceLog::class, 'document_id')->where('document_type', 'rafaksi')->orderBy('action_at', 'desc');
    }


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
        return $this->belongsToMany(Toko::class, 'rafaksi_toko', 'rafaksi_id', 'toko_id');
    }

    public function items()
    {
        return $this->hasMany(RafaksiItem::class);
    }

    public function documents()
    {
        return $this->hasMany(RafaksiDocument::class);
    }

    public function getDaftarTokoFormattedAttribute()
    {
        // Jika tidak ada toko yang berelasi, kembalikan strip
        if ($this->tokos->isEmpty()) {
            return '-';
        }

        // Kalau cuma 1 toko yang dipilih, tampilkan nama toko itu sendiri -- label
        // MBA/LS cuma relevan kalau lagi ngegabungin beberapa toko sekaligus.
        if ($this->tokos->count() === 1) {
            return $this->tokos->first()->nama_toko;
        }

        // Klasifikasi klaim cuma ada 2: MBA (PT. Mitra Belanja Anda) atau LS (Lucky
        // Strategis -- representasi SEMUA toko non-MBA, terlepas dari nama_pt
        // aslinya masing-masing, karena yang nge-claim toko-toko itu memang LS).
        $isMba = fn ($toko) => str_contains(strtoupper($toko->nama_pt ?? ''), 'MITRA BELANJA ANDA');

        if ($this->tokos->every($isMba)) {
            return 'MBA';
        }

        if ($this->tokos->every(fn ($toko) => ! $isMba($toko))) {
            return 'LS';
        }

        // Campuran MBA & non-MBA -- seharusnya gak kejadian di alur bisnis normal,
        // tapi kalau kejadian, fallback list nama toko biar gak nyesatin.
        return $this->tokos->pluck('nama_toko')->implode(', ');
    }

    public function categories(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function reminder(){
        return $this->belongsTo(MasterNotifikasiReminder::class, 'reminder_id', 'id');
    }
}
