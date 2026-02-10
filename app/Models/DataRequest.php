<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRequest extends Model
{
    use HasFactory;

    protected $table = 'data_requests';

    protected $fillable = [
        'user_id',
        'form_id',
        'kode_pengajuan',
        'tanggal_pengajuan',
        'aplikasi',
        'username_akun',
        'nomor_hp',
        'email_lama',
        'email_baru',
        'nama_pemohon',
        'riwayat_transaksi',
        'saldo_terakhir',
        'jenis_perubahan',
        'alasan_perubahan',
        'foto_ktp',
        'foto_selfie',
        'wa_penerima',
        'wa_pengisi',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'approved_at' => 'datetime',
        'saldo_terakhir' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(DataRequestForm::class, 'form_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
