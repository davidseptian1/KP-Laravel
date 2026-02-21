<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_nomor_pasca_bayar_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_nomor_pasca_bayar_id')->constrained('tag_nomor_pasca_bayars')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('tagihan', 15, 2)->nullable();
            $table->string('bank')->nullable();
            $table->date('tanggal_payment')->nullable();
            $table->timestamps();
            $table->unique(['tag_nomor_pasca_bayar_id', 'periode_bulan', 'periode_tahun'], 'pasca_period_unique');
        });

        $rows = DB::table('tag_nomor_pasca_bayars')->get();
        foreach ($rows as $row) {
            if (!is_null($row->periode_des_2025_tagihan) || !empty($row->periode_des_2025_bank)) {
                DB::table('tag_nomor_pasca_bayar_periods')->insert([
                    'tag_nomor_pasca_bayar_id' => $row->id,
                    'periode_bulan' => 12,
                    'periode_tahun' => 2025,
                    'tagihan' => $row->periode_des_2025_tagihan,
                    'bank' => $row->periode_des_2025_bank,
                    'tanggal_payment' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!is_null($row->periode_feb_2026_tagihan) || !empty($row->periode_feb_2026_tanggal_payment)) {
                DB::table('tag_nomor_pasca_bayar_periods')->insert([
                    'tag_nomor_pasca_bayar_id' => $row->id,
                    'periode_bulan' => 2,
                    'periode_tahun' => 2026,
                    'tagihan' => $row->periode_feb_2026_tagihan,
                    'bank' => null,
                    'tanggal_payment' => $row->periode_feb_2026_tanggal_payment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_nomor_pasca_bayar_periods');
    }
};
