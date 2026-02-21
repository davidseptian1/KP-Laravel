<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_pln_internet_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_pln_internet_id')->constrained('tag_pln_internets')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('tagihan', 15, 2)->nullable();
            $table->date('tanggal_payment')->nullable();
            $table->timestamps();
            $table->unique(['tag_pln_internet_id', 'periode_bulan', 'periode_tahun'], 'pln_period_unique');
        });

        $rows = DB::table('tag_pln_internets')->get();
        foreach ($rows as $row) {
            if (!is_null($row->periode_januari_2026_tagihan) || !empty($row->periode_januari_2026_tanggal_payment)) {
                DB::table('tag_pln_internet_periods')->insert([
                    'tag_pln_internet_id' => $row->id,
                    'periode_bulan' => 1,
                    'periode_tahun' => 2026,
                    'tagihan' => $row->periode_januari_2026_tagihan,
                    'tanggal_payment' => $row->periode_januari_2026_tanggal_payment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!is_null($row->periode_februari_2026_tagihan) || !empty($row->periode_februari_2026_tanggal_payment)) {
                DB::table('tag_pln_internet_periods')->insert([
                    'tag_pln_internet_id' => $row->id,
                    'periode_bulan' => 2,
                    'periode_tahun' => 2026,
                    'tagihan' => $row->periode_februari_2026_tagihan,
                    'tanggal_payment' => $row->periode_februari_2026_tanggal_payment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_pln_internet_periods');
    }
};
