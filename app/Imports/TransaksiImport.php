<?php

namespace App\Imports;

use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
// Removed queueing concern to avoid dependency mismatch with installed package
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

class TransaksiImport implements ToCollection, WithHeadingRow, WithChunkReading, WithCustomValueBinder
{
    private $importId = null;

    public function __construct($importId = null)
    {
        $this->importId = $importId;
    }

    public function setImportId($id)
    {
        $this->importId = $id;
        return $this;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return false;
    }

    public function collection(Collection $rows)
    {
        // helper: normalize header keys and parse numeric/currency values
        $normalizeRow = function ($row) {
            $out = [];
            foreach ($row->toArray() as $k => $v) {
                $key = trim(strtolower($k));
                $key = preg_replace('/[^a-z0-9\s_]/i', '', $key);
                $key = str_replace([' ', '-'], '_', $key);

                // map common truncated/misspelled headings to canonical keys
                if (strpos($key, 'trx') !== false && strpos($key, 'id') !== false) {
                    $key = 'trx_id';
                } elseif (strpos($key, 'tgl') !== false && strpos($key, 'ent') !== false) {
                    $key = 'tgl_entri';
                } elseif (strpos($key, 'kode') !== false && strpos($key, 'produk') !== false) {
                    $key = 'kode_produk';
                } elseif (strpos($key, 'kode') !== false && strpos($key, 'produ') !== false) {
                    $key = 'kode_produk';
                } elseif (strpos($key, 'nomor') !== false) {
                    // nomor tujuan / nomor tuju
                    $key = 'nomor_tujuan';
                } elseif (strpos($key, 'kode') !== false && strpos($key, 'resel') !== false) {
                    $key = 'kode_reseller';
                } elseif (strpos($key, 'nama') !== false && strpos($key, 'resel') !== false) {
                    $key = 'nama_reseller';
                } elseif (strpos($key, 'harga') !== false && strpos($key, 'beli') !== false) {
                    $key = 'harga_beli';
                } elseif (strpos($key, 'harga') !== false && strpos($key, 'jual') !== false) {
                    $key = 'harga_jual';
                } elseif (strpos($key, 'laba') !== false) {
                    $key = 'laba';
                }

                $out[$key] = $v;
            }

            return $out;
        };

        $parseNumber = function ($value) {
            if ($value === null) return 0.0;
            $s = trim((string) $value);
            if ($s === '') return 0.0;

            // Remove currency symbol and whitespace
            $s = preg_replace('/[Rr][Pp]|[^0-9,\.\-Ee\+]/u', '', $s);

            // If contains comma as decimal or scientific with comma (e.g. 1,18E+08), convert
            // Remove thousand separator dots, convert decimal comma to dot
            // But keep exponent marker E/e
            // Workaround: if string contains 'E' or 'e' and also contains ',', replace only first comma to dot
            if (preg_match('/[Ee]/', $s)) {
                // change comma to dot (if present) e.g. 1,18E+08 -> 1.18E+08
                $s = preg_replace('/,/', '.', $s, 1);
                $s = str_replace('.', '.', $s); // no-op but keeps intent
                // remove any remaining non-numeric except e/E +- and dot
                $s = preg_replace('/[^0-9eE\+\-\.]/', '', $s);
            } else {
                // remove dots used as thousand separators
                $s = str_replace('.', '', $s);
                // convert comma decimal to dot
                $s = str_replace(',', '.', $s);
                // strip anything else
                $s = preg_replace('/[^0-9\-\.]/', '', $s);
            }

            if ($s === '' || $s === '-' ) return 0.0;
            return floatval($s);
        };
        // Prepare batch inserts: build array of rows, skip empty headings
        $insert = [];
        $trxIds = [];

        foreach ($rows as $rowObj) {
            $rowArr = $normalizeRow($rowObj);

            if (empty($rowArr['trx_id']) || empty($rowArr['tgl_entri'])) {
                continue;
            }

            // normalize trx_id: handle scientific/excel formatting
            $rawTrx = $rowArr['trx_id'];
            $trxId = (string) $rawTrx;
            if (preg_match('/[Ee]/', (string)$rawTrx) || preg_match('/^[0-9,.]+$/', (string)$rawTrx)) {
                $num = $parseNumber($rawTrx);
                $trxId = (string) intval(round($num));
            }

            // Parse tanggal (supports d/m/Y H:i[:s])
            try {
                $tglRaw = trim((string) ($rowArr['tgl_entri'] ?? ''));
                if (preg_match('/\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}:\d{2}/', $tglRaw)) {
                    $tglEntri = Carbon::createFromFormat('d/m/Y H:i:s', $tglRaw);
                } else {
                    $tglEntri = Carbon::createFromFormat('d/m/Y H:i', $tglRaw);
                }
            } catch (\Exception $e) {
                $tglEntri = now();
            }

            $hargaBeli = $parseNumber($rowArr['harga_beli'] ?? 0);
            $hargaJual = $parseNumber($rowArr['harga_jual'] ?? 0);
            $laba = $parseNumber($rowArr['laba'] ?? null);
            if (empty($laba)) {
                $laba = $hargaJual - $hargaBeli;
            }

            $insert[] = [
                'import_id' => $this->importId,
                'trx_id' => $trxId,
                'tgl_entri' => $tglEntri->format('Y-m-d H:i:s'),
                'kode_produk' => $rowArr['kode_produk'] ?? 'UNKNOWN',
                'nomor_tujuan' => $rowArr['nomor_tujuan'] ?? '',
                'status' => $rowArr['status'] ?? 'Proses',
                'sn' => $rowArr['sn'] ?? null,
                'kode_reseller' => $rowArr['kode_reseller'] ?? null,
                'nama_reseller' => $rowArr['nama_reseller'] ?? null,
                'modul' => $rowArr['modul'] ?? null,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'laba' => $laba,
                'durasi_detik' => is_numeric($rowArr['durasi_detik'] ?? null) ? intval($rowArr['durasi_detik']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $trxIds[] = $trxId;
        }

        if (empty($insert)) {
            return;
        }

        // Remove trx_id that already exist in DB to avoid unique constraint errors
        $existing = DB::table('transaksis')->whereIn('trx_id', array_values($trxIds))->pluck('trx_id')->all();

        $toInsert = array_filter($insert, function ($r) use ($existing) {
            return !in_array($r['trx_id'], $existing);
        });

        if (empty($toInsert)) {
            return;
        }

        // Insert in chunks to avoid huge single query
        $chunks = array_chunk($toInsert, 1000);
        foreach ($chunks as $c) {
            DB::table('transaksis')->insertOrIgnore($c);
        }
    }

    public function rules(): array
    {
        return [
            // Validation is intentionally minimal to avoid heavy DB checks per row during large imports.
            'trx_id' => 'required',
            'tgl_entri' => 'required',
            'kode_produk' => 'required',
            'status' => 'required|in:Gagal,Sukses,Proses',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'trx_id.required' => 'ID Transaksi harus diisi',
            'trx_id.unique' => 'ID Transaksi sudah ada di database',
            'tgl_entri.required' => 'Tanggal Entry harus diisi',
            'kode_produk.required' => 'Kode Produk harus diisi',
            'status.required' => 'Status transaksi harus diisi',
            'status.in' => 'Status harus salah satu: Gagal, Sukses, Proses',
            'harga_beli.required' => 'Harga Beli / Harga Modal harus diisi',
            'harga_beli.numeric' => 'Harga Beli / Harga Modal harus berupa angka',
            'harga_jual.required' => 'Harga Jual harus diisi',
            'harga_jual.numeric' => 'Harga Jual harus berupa angka',
        ];
    }

    /**
     * Batch size for bulk insert - reduces number of DB queries.
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Chunk size for reading the file - limits memory usage.
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
