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
        switch ($this->status) {
            case 'success':
                return 'Sukses Semua';
            case 'partial_failed':
                return 'Sebagian Gagal';
            case 'failed':
                return 'Gagal Total';
            default:
                return ucfirst((string) $this->status);
        }
    }

    /**
     * Warna badge status (Tailwind)
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case 'success':
                return 'bg-green-100 text-green-800 border-green-200';
            case 'partial_failed':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'failed':
                return 'bg-red-100 text-red-800 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
}

