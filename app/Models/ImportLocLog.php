<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLocLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'failed_rows'   => 'array',
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
        'total_rows'    => 'integer',
        'success_count' => 'integer',
        'skipped_count' => 'integer',
        'failed_count'  => 'integer',
    ];

    /**
     * Label status yang user-friendly
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'success'        => 'Sukses Semua',
            'partial_failed' => 'Sebagian Gagal',
            'failed'         => 'Gagal Total',
            default          => ucfirst($this->status),
        };
    }

    /**
     * Warna badge status (Tailwind)
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'success'        => 'bg-green-100 text-green-800 border-green-200',
            'partial_failed' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'failed'         => 'bg-red-100 text-red-800 border-red-200',
            default          => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}

