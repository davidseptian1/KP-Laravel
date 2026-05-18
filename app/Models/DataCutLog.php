<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCutLog extends Model
{
    use HasFactory;

    protected $table = 'data_cut_logs';

    protected $fillable = [
        'user_id',
        'backup_file',
        'backup_size',
        'cut_date',
        'transaksis_deleted',
        'deposits_deleted',
        'reimburse_deleted',
        'minusans_deleted',
        'imports_deleted',
        'tag_nomor_pasca_bayars_deleted',
        'tag_pln_internets_deleted',
        'tag_lainnyas_deleted',
        'notes',
        'status',
        'error_log',
    ];

    protected $casts = [
        'cut_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalDeletedCount()
    {
        return $this->transaksis_deleted +
               $this->deposits_deleted +
               $this->reimburse_deleted +
               $this->minusans_deleted +
               $this->imports_deleted +
               $this->tag_nomor_pasca_bayars_deleted +
               $this->tag_pln_internets_deleted +
               $this->tag_lainnyas_deleted;
    }

    public function getBackupSizeFormatted()
    {
        if (!$this->backup_size) {
            return 'N/A';
        }

        $bytes = (int) str_replace(' MB', '', $this->backup_size) * 1024 * 1024;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
