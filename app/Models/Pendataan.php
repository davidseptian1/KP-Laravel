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

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUKSES = 'sukses';
    public const STATUS_GAGAL = 'gagal';

    protected $table = 'pendataans';

    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi',
        'nama_produk',
        'jenis_chip',
        'harga_qty',
        'total_harga',
        'qty',
        'gambar',
        'alasan_edit',
        'status',
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

    /**
     * Get readable label for status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUKSES => 'Sukses',
            self::STATUS_GAGAL => 'Gagal',
            default => 'Pending',
        };
    }

    /**
     * Get badge HTML for status.
     */
    public function getStatusBadgeHtmlAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUKSES => '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fw-bold"><i class="ti ti-check me-1"></i>Sukses ✅</span>',
            self::STATUS_GAGAL => '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1 fw-bold"><i class="ti ti-x me-1"></i>Gagal ❌</span>',
            default => '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 fw-bold"><i class="ti ti-clock me-1"></i>Pending ⏳</span>',
        };
    }
}
