<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramPendataanLog extends Model
{
    use HasFactory;

    public const STATUS_MATCHED = 'matched';
    public const STATUS_UNMATCHED = 'unmatched';
    public const STATUS_GENERAL_CHAT = 'general_chat';
    public const STATUS_INVALID_FORMAT = 'invalid_format';
    public const STATUS_ERROR = 'error';

    protected $table = 'telegram_pendataan_logs';

    protected $fillable = [
        'chat_id',
        'chat_title',
        'message_id',
        'sender_username',
        'sender_name',
        'raw_message',
        'parsed_product',
        'parsed_nominal',
        'raw_nominal',
        'status',
        'pendataan_id',
        'action_note',
        'bot_replied',
        'bot_reply_text',
    ];

    protected $casts = [
        'parsed_nominal' => 'float',
        'bot_replied' => 'boolean',
        'message_id' => 'integer',
        'pendataan_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to the matched Pendataan record.
     */
    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class, 'pendataan_id');
    }

    /**
     * Status badge HTML for admin tables.
     */
    public function getStatusBadgeHtmlAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MATCHED => '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1"><i class="ti ti-circle-check me-1"></i>Cocok ✅</span>',
            self::STATUS_UNMATCHED => '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1"><i class="ti ti-hourglass-empty me-1"></i>Belum Cocok ⏳</span>',
            self::STATUS_GENERAL_CHAT, self::STATUS_INVALID_FORMAT => '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-1"><i class="ti ti-message-2 me-1"></i>Pesan Umum 💬</span>',
            self::STATUS_ERROR => '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1"><i class="ti ti-alert-triangle me-1"></i>Error ❌</span>',
            default => '<span class="badge bg-light text-dark px-2 py-1">' . e($this->status) . '</span>',
        };
    }

    /**
     * Human-friendly status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MATCHED => 'Cocok (Sukses)',
            self::STATUS_UNMATCHED => 'Belum Menemukan Kecocokan',
            self::STATUS_GENERAL_CHAT, self::STATUS_INVALID_FORMAT => 'Pesan Umum / Chat (Tidak Dicocokkan)',
            self::STATUS_ERROR => 'Terjadi Kesalahan',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Formatted Rupiah nominal.
     */
    public function getFormattedNominalAttribute(): string
    {
        if ($this->parsed_nominal === null || $this->parsed_nominal <= 0) {
            return '-';
        }

        return 'Rp ' . number_format($this->parsed_nominal, 0, ',', '.');
    }

    /**
     * Formatted datetime string.
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i:s') : '-';
    }
}
