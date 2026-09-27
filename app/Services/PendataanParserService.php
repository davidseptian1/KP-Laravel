<?php

namespace App\Services;

class PendataanParserService
{
    /**
     * Parse raw description text and extract product, price, quantity, and total.
     *
     * @param string|null $text
     * @return array{nama_produk: string, harga_qty: float, total_harga: float, qty: int}
     */
    public static function parse(?string $text): array
    {
        $result = [
            'nama_produk' => '',
            'harga_qty' => 0.0,
            'total_harga' => 0.0,
            'qty' => 1,
        ];

        if (empty($text)) {
            return $result;
        }

        $cleanText = str_replace(["\r\n", "\r"], "\n", trim($text));

        // 1. Check for labeled format first
        if (preg_match('/(?:Nama\s*Produk|Produk)\s*[:=]\s*(.+)/i', $cleanText, $m)) {
            $result['nama_produk'] = trim($m[1]);
        }

        if (preg_match('/(?:Harga\s*Qty|Harga\s*Satuan|Harga\s*Per\s*Qty)\s*[:=]\s*(?:Rp\.?\s*)?([\d.,]+)/i', $cleanText, $m)) {
            $result['harga_qty'] = self::cleanPrice($m[1]);
        }

        if (preg_match('/(?:Total\s*Tagihan|Total\s*Harga|Total\s*Pembayaran|Total\s*Bayar)\s*[:=]?\s*\n?\s*(?:Rp\.?\s*)?([\d.,]+)/i', $cleanText, $m)) {
            $result['total_harga'] = self::cleanPrice($m[1]);
        }

        if (preg_match('/(?:Total\s*Qty|Qty|Jumlah)\s*[:=]?\s*\n?\s*(\d+)/i', $cleanText, $m)) {
            $result['qty'] = (int) $m[1];
        }

        // 2. Extract Qty from +/- stepper block: e.g. "- \n 200 \n +" or just "- \n 200"
        // Require the "-" to be at the start of a line (avoids matching "5hr - 15.000").
        // The trailing "+" is made optional so qty is captured even if user only copied "-\nQTY".
        if ($result['qty'] <= 1) {
            if (preg_match('/(?:^|\n)[ \t]*[-–—][ \t]*\n[ \t]*(\d+)[ \t]*(?:\n[ \t]*[+＋])?/m', $cleanText, $m)) {
                $result['qty'] = (int) $m[1];
            }
        }

        // 2b. Text-based stepper: Indonesian app format
        // e.g. "kurangi jumlah\n10\ntambah jumlah" or "kurangi\n10\ntambah"
        if ($result['qty'] <= 1) {
            if (preg_match('/(?:kurangi(?:\s+jumlah)?)\s*\n\s*(\d+)\s*\n\s*(?:tambah(?:\s+jumlah)?)/i', $cleanText, $m)) {
                $result['qty'] = (int) $m[1];
            }
        }

        // 3. Fallback extraction from lines (mobile app format)
        $lines = array_map('trim', explode("\n", $cleanText));
        $nonEmptyLines = array_values(array_filter($lines, function ($l) {
            return $l !== '';
        }));

        // Find product name if not set
        if (empty($result['nama_produk'])) {
            $ignoreKeywords = [
                'konfirmasi', 'detail transaksi', 'rincian transaksi', 'metode pembayaran',
                'saldo dompul', 'pin dompul', 'pin keuangan', 'masukkan pin',
                'total tagihan', 'total qty', 'total bayar', 'pembayaran', 'ringkasan',
                'kurangi jumlah', 'tambah jumlah', 'kurangi', 'hapus item',
            ];

            foreach ($nonEmptyLines as $line) {
                $lower = strtolower($line);
                if (in_array($line, ['-', '+', '–', '—', '＋'])) {
                    continue;
                }
                if (is_numeric($line)) {
                    continue;
                }
                if (preg_match('/^(?:rp\.?|idr)\s*[\d.,]+/i', $line)) {
                    continue;
                }

                $isIgnored = false;
                foreach ($ignoreKeywords as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $result['nama_produk'] = $line;
                    break;
                }
            }
        }

        // Find line after Total Tagihan
        if ($result['total_harga'] <= 0) {
            for ($i = 0; $i < count($nonEmptyLines); $i++) {
                if (preg_match('/Total\s*(?:Tagihan|Bayar|Pembayaran|Harga)/i', $nonEmptyLines[$i])) {
                    // check current line
                    if (preg_match('/(?:Rp\.?\s*)?([\d.,]+)/i', $nonEmptyLines[$i], $m) && self::cleanPrice($m[1]) > 0) {
                        $result['total_harga'] = self::cleanPrice($m[1]);
                        break;
                    }
                    // or check next line
                    if (isset($nonEmptyLines[$i + 1])) {
                        $p = self::cleanPrice($nonEmptyLines[$i + 1]);
                        if ($p > 0) {
                            $result['total_harga'] = $p;
                            break;
                        }
                    }
                }
            }
        }

        // Find line after Total Qty
        if ($result['qty'] <= 1) {
            for ($i = 0; $i < count($nonEmptyLines); $i++) {
                if (preg_match('/Total\s*Qty/i', $nonEmptyLines[$i])) {
                    if (preg_match('/(\d+)/', $nonEmptyLines[$i], $m)) {
                        $result['qty'] = (int) $m[1];
                        break;
                    }
                    if (isset($nonEmptyLines[$i + 1]) && is_numeric($nonEmptyLines[$i + 1])) {
                        $result['qty'] = (int) $nonEmptyLines[$i + 1];
                        break;
                    }
                }
            }
        }

        // Find unit price (harga qty)
        if ($result['harga_qty'] <= 0) {
            // Check lines after product name and before 'Metode Pembayaran' or 'Total Tagihan'
            $foundProduct = empty($result['nama_produk']);
            for ($i = 0; $i < count($nonEmptyLines); $i++) {
                if (!$foundProduct) {
                    if ($nonEmptyLines[$i] === $result['nama_produk']) {
                        $foundProduct = true;
                    }
                    continue;
                }

                if (preg_match('/(?:Metode\s*Pembayaran|Total\s*(?:Tagihan|Bayar|Pembayaran))/i', $nonEmptyLines[$i])) {
                    break; // Stop before payment method or total
                }

                if (self::isPriceLine($nonEmptyLines[$i])) {
                    $p = self::cleanPrice($nonEmptyLines[$i]);
                    if ($p > 0) {
                        $result['harga_qty'] = $p;
                        break;
                    }
                }
            }
        }

        // Cross-calculate if one is missing
        if ($result['harga_qty'] <= 0 && $result['total_harga'] > 0 && $result['qty'] > 0) {
            $result['harga_qty'] = round($result['total_harga'] / $result['qty'], 2);
        }

        if ($result['total_harga'] <= 0 && $result['harga_qty'] > 0 && $result['qty'] > 0) {
            $result['total_harga'] = round($result['harga_qty'] * $result['qty'], 2);
        }

        return $result;
    }

    /**
     * Check if a string is genuinely a price line.
     */
    public static function isPriceLine(string $line): bool
    {
        $line = trim($line);
        // Explicit Rp or IDR
        if (preg_match('/^(?:Rp\.?|IDR)?\s*[\d.,]+\s*$/i', $line)) {
            // Must have digits
            return (bool) preg_match('/\d/', $line);
        }

        if (preg_match('/(?:Rp\.?|IDR)\s*[\d.,]+/i', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Clean numeric price from formatted string.
     */
    public static function cleanPrice(string $raw): float
    {
        $raw = trim($raw);
        $raw = preg_replace('/[^\d.,]/', '', $raw);
        if (empty($raw)) {
            return 0.0;
        }

        if (str_contains($raw, '.') && str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } elseif (str_contains($raw, '.')) {
            $parts = explode('.', $raw);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $raw = str_replace('.', '', $raw);
            }
        } elseif (str_contains($raw, ',')) {
            $parts = explode(',', $raw);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $raw = str_replace(',', '', $raw);
            } else {
                $raw = str_replace(',', '.', $raw);
            }
        }

        return (float) $raw;
    }
}
