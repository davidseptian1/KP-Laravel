<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosmedSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'username_sosmed',
        'nama_first_word',
        'divisi',
        'sosmed_platform',
        'pilihan_tugas',
        'tugas_link',
        'photos',
        'status',
        'catatan',
        'fee_amount',
        'processed_by',
        'processed_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'fee_amount' => 'float',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public static function extractFirstWord(string $nama): string
    {
        $cleaned = trim($nama);
        if (empty($cleaned)) {
            return '';
        }
        $parts = preg_split('/\s+/', $cleaned);
        return $parts[0] ?? $cleaned;
    }
}
