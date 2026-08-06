<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosmedSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nama_first_word',
        'divisi',
        'sosmed_platform',
        'photos',
        'status',
        'catatan',
        'fee_amount',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'fee_amount' => 'float',
        'processed_at' => 'datetime',
    ];

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
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
