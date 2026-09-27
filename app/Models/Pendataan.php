<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pendataan extends Model
{
    use HasFactory;

    /**
     * Predefined staff names for Pendataan
     */
    public const DAFTAR_NAMA = [
        'Ginta',
        'Sinta',
        'Reno',
        'Dira',
        'Pise',
        'Rani',
        'Diah',
        'Diya',
        'Rafi',
        'Rudi',
        'Khodam',
        'Nadir',
        'Nuni',
    ];

    protected $table = 'pendataans';

    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'nama_produk',
        'harga_qty',
        'total_harga',
        'qty',
        'gambar',
    ];

    protected $casts = [
        'harga_qty' => 'float',
        'total_harga' => 'float',
        'qty' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get formatted unit price.
     */
    public function getFormattedHargaQtyAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_qty, 0, ',', '.');
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalHargaAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    /**
     * Get full URL or asset path for uploaded image.
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (!$this->gambar) {
            return null;
        }

        if (filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        return asset('storage/' . $this->gambar);
    }
}
