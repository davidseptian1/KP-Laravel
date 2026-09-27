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

        // 2. Lines breakdown and stepper detection
        $lines = array_map('trim', explode("\n", $cleanText));
        $nonEmptyLines = array_values(array_filter($lines, function ($l) {
            return $l !== '';
        }));

        $stepperStartIndex = null;
        $stepperEndIndex = null;
        for ($i = 0; $i < count($nonEmptyLines); $i++) {
            $line = $nonEmptyLines[$i];
            // Symbol stepper: "-" \n QTY [\n "+"]
            if (in_array($line, ['-', '–', '—']) && isset($nonEmptyLines[$i + 1]) && ctype_digit($nonEmptyLines[$i + 1])) {
                $stepperStartIndex = $i;
                $stepperEndIndex = (isset($nonEmptyLines[$i + 2]) && in_array($nonEmptyLines[$i + 2], ['+', '＋'])) ? $i + 2 : $i + 1;
                if ($result['qty'] <= 1) {
                    $result['qty'] = (int) $nonEmptyLines[$i + 1];
                }
                break;
            }
            // Text stepper: "kurangi [jumlah]" \n QTY [\n "tambah [jumlah]"]
            if (preg_match('/^kurangi(?:\s+jumlah)?$/i', $line) && isset($nonEmptyLines[$i + 1]) && ctype_digit($nonEmptyLines[$i + 1])) {
                $stepperStartIndex = $i;
                $stepperEndIndex = (isset($nonEmptyLines[$i + 2]) && preg_match('/^tambah(?:\s+jumlah)?$/i', $nonEmptyLines[$i + 2])) ? $i + 2 : $i + 1;
                if ($result['qty'] <= 1) {
                    $result['qty'] = (int) $nonEmptyLines[$i + 1];
                }
                break;
            }
        }

        // Find product name if not set
        $productIndex = 0;
        if (empty($result['nama_produk'])) {
            $ignoreKeywords = [
                'konfirmasi', 'detail transaksi', 'rincian transaksi', 'metode pembayaran',
                'saldo dompul', 'pin dompul', 'pin keuangan', 'masukkan pin',
                'total tagihan', 'total qty', 'total bayar', 'pembayaran', 'ringkasan',
                'kurangi jumlah', 'tambah jumlah', 'kurangi', 'hapus item',
            ];

            foreach ($nonEmptyLines as $idx => $line) {
                $lower = strtolower($line);
                if (in_array($line, ['-', '+', '–', '—', '＋'])) continue;
                if (is_numeric($line)) continue;
                if (preg_match('/^(?:rp\.?|idr)\s*[\d.,]+/i', $line)) continue;

                $isIgnored = false;
                foreach ($ignoreKeywords as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $result['nama_produk'] = $line;
                    $productIndex = $idx;
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

        // Collect all price candidates outside of the stepper lines
        $pricesBeforeStepper = [];
        $pricesAfterStepper = [];
        $allPriceCandidates = [];

        foreach ($nonEmptyLines as $idx => $line) {
            if ($idx === $productIndex) continue;
            if ($stepperStartIndex !== null && $idx >= $stepperStartIndex && $idx <= $stepperEndIndex) {
                continue;
            }
            if (self::isPriceLine($line)) {
                $p = self::cleanPrice($line);
                if ($p > 0) {
                    $allPriceCandidates[] = ['index' => $idx, 'price' => $p];
                    if ($stepperStartIndex !== null) {
                        if ($idx < $stepperStartIndex) {
                            $pricesBeforeStepper[] = $p;
                        } elseif ($idx > $stepperEndIndex) {
                            $pricesAfterStepper[] = $p;
                        }
                    }
                }
            }
        }

        $qty = $result['qty'] > 0 ? $result['qty'] : 1;

        // Resolve prices based on stepper position
        if ($stepperStartIndex !== null) {
            if (!empty($pricesBeforeStepper)) {
                // Case 1: Price BEFORE stepper is the unit price (e.g. Flex Mini)
                if ($result['harga_qty'] <= 0) {
                    $result['harga_qty'] = $pricesBeforeStepper[0];
                }
                if ($result['total_harga'] <= 0) {
                    if (!empty($pricesAfterStepper)) {
                        $result['total_harga'] = end($pricesAfterStepper);
                    } else {
                        $result['total_harga'] = round($result['harga_qty'] * $qty, 2);
                    }
                }
            } elseif (!empty($pricesAfterStepper)) {
                // Case 2: NO price before stepper; price(s) appear AFTER stepper (e.g. Kuota Nonstop)
                $uniquePrices = array_values(array_unique($pricesAfterStepper));
                if (count($uniquePrices) >= 2) {
                    sort($uniquePrices);
                    $pSmall = $uniquePrices[0];
                    $pLarge = end($uniquePrices);
                    if (abs(($pSmall * $qty) - $pLarge) < 2) {
                        if ($result['harga_qty'] <= 0) $result['harga_qty'] = $pSmall;
                        if ($result['total_harga'] <= 0) $result['total_harga'] = $pLarge;
                    } else {
                        if ($result['total_harga'] <= 0) $result['total_harga'] = $pLarge;
                        if ($result['harga_qty'] <= 0) $result['harga_qty'] = round($pLarge / $qty, 2);
                    }
                } else {
                    // All prices after stepper are identical (or only 1 price exists).
                    // Because there was NO price before stepper, this price is TOTAL HARGA!
                    $totalCandidate = $pricesAfterStepper[0];
                    if ($result['total_harga'] <= 0) {
                        $result['total_harga'] = $totalCandidate;
                    }
                    if ($result['harga_qty'] <= 0) {
                        $result['harga_qty'] = round($result['total_harga'] / $qty, 2);
                    }
                }
            }
        } else {
            // No stepper detected
            if ($result['harga_qty'] <= 0 && !empty($allPriceCandidates)) {
                $uniquePrices = array_values(array_unique(array_column($allPriceCandidates, 'price')));
                if ($qty > 1 && count($uniquePrices) === 1) {
                    // Only 1 unique price with qty > 1
                    if ($result['total_harga'] <= 0) {
                        $result['total_harga'] = $uniquePrices[0];
                    }
                    $result['harga_qty'] = round($result['total_harga'] / $qty, 2);
                } else {
                    $result['harga_qty'] = $allPriceCandidates[0]['price'];
                }
            }
        }

        // Safety checks & cross-calculation
        if ($result['total_harga'] <= 0 && $result['harga_qty'] > 0 && $qty > 0) {
            $result['total_harga'] = round($result['harga_qty'] * $qty, 2);
        }
        if ($result['harga_qty'] <= 0 && $result['total_harga'] > 0 && $qty > 0) {
            $result['harga_qty'] = round($result['total_harga'] / $qty, 2);
        }

        // If qty > 1 and harga_qty was accidentally set equal to total_harga
        if ($qty > 1 && $result['total_harga'] > 0 && $result['harga_qty'] == $result['total_harga']) {
            $result['harga_qty'] = round($result['total_harga'] / $qty, 2);
        }

        return $result;
    }

    /**
     * Check if a string is genuinely a price line.
     */
    public static function isPriceLine(string $line): bool
    {
        $line = trim($line);

        // Line explicitly starts with Rp or IDR → always a price line
        if (preg_match('/^(?:Rp\.?|IDR)\s*[\d.,]+\s*$/i', $line)) {
            return (bool) preg_match('/\d/', $line);
        }

        // Embedded Rp / IDR anywhere on the line
        if (preg_match('/(?:Rp\.?|IDR)\s*[\d.,]+/i', $line)) {
            return true;
        }

        // Bare number (no prefix): only treat as price if it contains a
        // thousands separator (. or ,) — e.g. "624.990" yes, "10" no.
        if (preg_match('/^[\d.,]+$/', $line) && preg_match('/[.,]/', $line)) {
            return (bool) preg_match('/\d/', $line);
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
