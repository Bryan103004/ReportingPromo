<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'modules',
    //     'is_active',
    // ];

    protected $guarded = ['id'];

    protected $casts = [
        'modules'   => 'array',
        'filters'   => 'array', // Tambahkan ini
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Hanya penerima yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter penerima yang terdaftar pada modul tertentu.
     *
     * Contoh: NotificationRecipient::active()->forModule('kontrak')->get()
     */
    public function scopeForModule($query, $module)
    {
        return $query->whereJsonContains('modules', $module);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Cek apakah penerima ini terdaftar di modul tertentu.
     */
    public function hasModule($module)
    {
        return in_array($module, $this->modules ?? []);
    }
}