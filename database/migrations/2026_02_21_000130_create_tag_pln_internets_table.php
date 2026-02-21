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
        Schema::create('tag_pln_internets', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nomor_pln_internet', 50)->unique();
            $table->string('atas_nama');
            $table->string('bank')->nullable();
            $table->string('keterangan')->nullable();
            $table->decimal('periode_januari_2026_tagihan', 15, 2)->nullable();
            $table->date('periode_januari_2026_tanggal_payment')->nullable();
            $table->decimal('periode_februari_2026_tagihan', 15, 2)->nullable();
            $table->date('periode_februari_2026_tanggal_payment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_pln_internets');
    }
};
