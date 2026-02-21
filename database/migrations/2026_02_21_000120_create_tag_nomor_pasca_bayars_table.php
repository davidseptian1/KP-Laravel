<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tag_nomor_pasca_bayars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30);
            $table->string('atas_nama');
            $table->string('chip')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('bank')->nullable();
            $table->string('status')->default('Aktif');
            $table->decimal('periode_des_2025_tagihan', 15, 2)->nullable();
            $table->string('periode_des_2025_bank')->nullable();
            $table->date('periode_feb_2026_tanggal_payment')->nullable();
            $table->decimal('periode_feb_2026_tagihan', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_nomor_pasca_bayars');
    }
};
