<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepositManualImport implements ToCollection, WithHeadingRow
{
    private string $inputDate;
    private ?int $userId;
    private int $insertedCount = 0;

    public function __construct(string $inputDate, ?int $userId = null)
    {
        $this->inputDate = $inputDate;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        $insertRows = [];

        foreach ($rows as $row) {
            $normalized = $this->normalizeRow($row->toArray());

            if (!$this->hasData($normalized)) {
                continue;
            }

            $namaSupplier = $this->cleanString($normalized['nama_supplier'] ?? null);
            if (!$namaSupplier || str_contains(strtolower($namaSupplier), 'supplier')) {
                continue;
            }

            $nominal = $this->parseNumber($normalized['nominal'] ?? null);
            if ($nominal <= 0) {
                continue;
            }

            $bank = $this->cleanString($normalized['bank'] ?? null) ?: '-';
            $server = $this->cleanString($normalized['server'] ?? null) ?: '-';
            $noRekRaw = $this->cleanString($normalized['no_rek'] ?? null) ?: '-';
            $noRekDigits = preg_replace('/\D+/', '', $noRekRaw);
            $noRek = $noRekDigits !== '' ? $noRekDigits : $noRekRaw;
            $namaRekening = $this->cleanString($normalized['nama_rekening'] ?? null) ?: '-';

            $replyTiket = $this->cleanString($normalized['reply_tiket'] ?? null);
            $replyPenambahan = $this->cleanString($normalized['reply_penambahan'] ?? null) ?: 'Input manual upload Excel';
            $jam = $this->parseTime($normalized['jam'] ?? null);

            $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->inputDate . ' ' . $jam);

            $insertRows[] = [
                'user_id' => $this->userId,
                'form_id' => null,
                'nama_supplier' => $namaSupplier,
                'jenis_transaksi' => 'deposit',
                'nominal' => $nominal,
                'bank' => $bank,
                'server' => $server,
                'no_rek' => $noRek,
                'nama_rekening' => $namaRekening,
                'reply_tiket' => $replyTiket,
                'reply_penambahan' => $replyPenambahan,
                'status' => 'selesai',
                'jam' => $jam,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ];
        }

        if (empty($insertRows)) {
            return;
        }

        foreach (array_chunk($insertRows, 500) as $chunk) {
            DB::table('deposits')->insert($chunk);
            $this->insertedCount += count($chunk);
        }
    }

    public function getInsertedCount(): int
    {
        return $this->insertedCount;
    }

    private function normalizeRow(array $row): array
    {
        $hasStringKey = false;
        foreach (array_keys($row) as $key) {
            if (!is_numeric($key)) {
                $hasStringKey = true;
                break;
            }
        }

        if (!$hasStringKey) {
            $cells = array_values($row);
            return [
                'nama_supplier' => $cells[1] ?? null,
                'nominal' => $cells[2] ?? null,
                'bank' => $cells[3] ?? null,
                'server' => $cells[4] ?? null,
                'no_rek' => $cells[5] ?? null,
                'nama_rekening' => $cells[6] ?? null,
                'reply_penambahan' => $cells[7] ?? null,
                'jam' => $cells[8] ?? null,
            ];
        }

        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeHeader((string) $key)] = $value;
        }

        return $normalized;
    }

    private function normalizeHeader(string $key): string
    {
        $key = strtolower(trim($key));
        $key = str_replace(['.', '-', '/', '\\', '  '], ' ', $key);
        $key = preg_replace('/\s+/', ' ', $key);
        $key = str_replace(' ', '_', $key);

        return match ($key) {
            'nama_suplier', 'nama_supplier' => 'nama_supplier',
            'no_rek', 'norek', 'no_rekening' => 'no_rek',
            'nama_rek', 'nama_rekening' => 'nama_rekening',
            'reply_penambahan', 'reply_penambahan_' => 'reply_penambahan',
            'reply_tiket', 'reply_ticket' => 'reply_tiket',
            'jam_', 'waktu', 'time' => 'jam',
            default => $key,
        };
    }

    private function hasData(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanString($value) !== null) {
                return true;
            }
        }

        return false;
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function parseNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0;
        }

        $raw = preg_replace('/[^0-9,\.\-]/', '', $raw);
        if ($raw === '' || $raw === '-') {
            return 0;
        }

        if (str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } else {
            $parts = explode('.', $raw);
            if (count($parts) > 2) {
                $raw = str_replace('.', '', $raw);
            }
        }

        return is_numeric($raw) ? (float) $raw : 0;
    }

    private function parseTime($value): string
    {
        if ($value === null || $value === '') {
            return '00:00:00';
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;

            if ($numeric > 0 && $numeric < 1) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numeric))->format('H:i:s');
            }

            $asText = rtrim(rtrim(number_format($numeric, 2, '.', ''), '0'), '.');
            if (preg_match('/^(\d{1,2})\.(\d{1,2})$/', $asText, $matches)) {
                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $minute = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                return "{$hour}:{$minute}:00";
            }
        }

        $text = trim((string) $value);

        if (preg_match('/^(\d{1,2})[\.:](\d{1,2})$/', $text, $matches)) {
            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $minute = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return "{$hour}:{$minute}:00";
        }

        if (preg_match('/^(\d{3,4})$/', $text)) {
            $text = str_pad($text, 4, '0', STR_PAD_LEFT);
            return substr($text, 0, 2) . ':' . substr($text, 2, 2) . ':00';
        }

        try {
            return Carbon::parse($text)->format('H:i:s');
        } catch (\Throwable $e) {
            return '00:00:00';
        }
    }
}
