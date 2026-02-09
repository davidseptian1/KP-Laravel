<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimburseForm extends Model
{
    use HasFactory;

    protected $table = 'reimburse_forms';

    protected $fillable = [
        'kode_form',
        'title',
        'description',
        'token',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reimburse()
    {
        return $this->hasMany(Reimburse::class, 'form_id');
    }
}
