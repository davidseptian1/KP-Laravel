<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Deposit extends Model
{
    use HasFactory;

    protected $table = 'deposits';

    protected $fillable = [
        'user_id',
        'form_id',
        'nama_supplier',
        'jenis_transaksi',
        'nominal',
        'bank',
        'bank_tujuan',
        'server',
        'no_rek',
        'nama_rekening',
        'reply_tiket',
        'reply_tiket_image',
        'reply_tiket_images',
        'reply_penambahan',
        'reply_penambahan_type',
        'reply_penambahan_image',
        'bukti_transfer_admin_type',
        'bukti_transfer_admin_text',
        'bukti_transfer_admin_image',
        'bukti_bayar_hutang_type',
        'bukti_bayar_hutang_text',
        'bukti_bayar_hutang_image',
        'status',
        'is_deleted_by_staff',
        'staff_deleted_note',
        'staff_deleted_at',
        'jam',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'jam' => 'datetime:H:i',
        'is_deleted_by_staff' => 'boolean',
        'staff_deleted_at' => 'datetime',
        'reply_tiket_images' => 'array',
    ];

    public function getReplyTiketImagesListAttribute(): array
    {
        if (is_array($this->reply_tiket_images) && !empty($this->reply_tiket_images)) {
            return array_values(array_filter($this->reply_tiket_images));
        }

        if (!empty($this->reply_tiket_image)) {
            return [$this->reply_tiket_image];
        }

        return [];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
