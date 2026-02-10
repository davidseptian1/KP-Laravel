<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;

    protected $table = 'loan_requests';

    protected $fillable = [
        'user_id',
        'form_id',
        'kode_pengajuan',
        'tanggal_pengajuan',
        'nama_server',
        'nomor_hp',
        'keperluan',
        'barang_dipinjam',
        'tanggal_pinjam',
        'tanggal_kembali',
        'wa_penerima',
        'wa_pengisi',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(LoanRequestForm::class, 'form_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
