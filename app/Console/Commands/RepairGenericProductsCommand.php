<?php

namespace App\Console\Commands;

use App\Models\Pendataan;
use App\Services\PendataanParserService;
use Illuminate\Console\Command;

class RepairGenericProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pendataan:repair-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaiki nama_produk yang sebelumnya tersimpan sebagai Keranjang Belanja atau kata umum dari deskripsi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $genericNames = ['keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama'];

        $records = Pendataan::whereNotNull('deskripsi')
            ->where('deskripsi', '!=', '')
            ->where(function ($query) use ($genericNames) {
                foreach ($genericNames as $name) {
                    $query->orWhere('nama_produk', 'like', $name);
                }
            })
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada data pendataan dengan nama produk umum (Keranjang Belanja) yang perlu diperbaiki.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$records->count()} data yang akan diperiksa dan diperbaiki...");

        $fixedCount = 0;
        foreach ($records as $record) {
            $parsed = PendataanParserService::parse($record->deskripsi);
            if (!empty($parsed['nama_produk'])) {
                $oldName = $record->nama_produk;
                $record->nama_produk = $parsed['nama_produk'];

                // Also update prices/qty if they were unset or 0
                if (($record->harga_qty <= 0) && $parsed['harga_qty'] > 0) {
                    $record->harga_qty = $parsed['harga_qty'];
                }
                if (($record->total_harga <= 0) && $parsed['total_harga'] > 0) {
                    $record->total_harga = $parsed['total_harga'];
                }
                if (($record->qty <= 1) && $parsed['qty'] > 1) {
                    $record->qty = $parsed['qty'];
                }

                $record->save();
                $this->line("✔ ID #{$record->id}: '{$oldName}' -> '{$record->nama_produk}'");
                $fixedCount++;
            }
        }

        $this->info("Selesai! {$fixedCount} data berhasil diperbaiki.");

        return Command::SUCCESS;
    }
}
