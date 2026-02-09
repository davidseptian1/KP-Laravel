<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        for ($i = 0; $i < 50000; $i++) {
            $data[] = [
                'kode_produk' => 'PROD-' . Str::random(5),
                'status' => ['Sukses', 'Gagal', 'Proses'][array_rand(['Sukses', 'Gagal', 'Proses'])],
                'tgl_entri' => now()->subDays(rand(0, 365)),
                'durasi_detik' => rand(10, 3600),
                'laba' => rand(1000, 100000),
                'harga_jual' => rand(10000, 1000000),
            ];
        }

        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $chunk) {
            DB::table('transaksis')->insert($chunk);
        }
    }
}
