<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramWebhookRawLog extends Model
{
    use HasFactory;

    protected $table = 'telegram_webhook_raw_logs';

    protected $fillable = [
        'source',
        'ip_address',
        'http_method',
        'update_id',
        'update_type',
        'chat_id',
        'chat_title',
        'sender_name',
        'summary',
        'raw_payload',
        'status',
        'notes',
    ];

    protected $casts = [
        'update_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get badge HTML for status.
     */
    public function getStatusBadgeHtmlAttribute(): string
    {
        return match ($this->status) {
            'matched' => '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1"><i class="ti ti-circle-check me-1"></i>Cocok ✅</span>',
            'unmatched' => '<span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle px-2 py-1"><i class="ti ti-clock me-1"></i>Belum Cocok ⏳</span>',
            'invalid_format' => '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1"><i class="ti ti-info-circle me-1"></i>Bukan SMS Voucher</span>',
            'ignored' => '<span class="badge bg-light text-muted border px-2 py-1"><i class="ti ti-ban me-1"></i>Diabaikan</span>',
            'error' => '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1"><i class="ti ti-alert-triangle me-1"></i>Error ❌</span>',
            default => '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-1"><i class="ti ti-arrow-down-left me-1"></i>Diterima</span>',
        };
    }
}
