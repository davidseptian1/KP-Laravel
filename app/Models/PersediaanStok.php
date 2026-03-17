<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersediaanStok extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'owner_name', 'bank_id', 'account_number', 'account_name',
        'purchase_date', 'receive_date', 'items', 'total_amount', 'on_behalf',
        'transfer_proof_path', 'invoice_text', 'invoice_path', 'status'
    ];

    protected $casts = [
        'items' => 'array',
        'purchase_date' => 'datetime',
        'receive_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];
}
