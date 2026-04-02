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
        Schema::create('rek_manuals', function (Blueprint $table) {
            $table->id();
            $table->string('bank_tujuan', 100);
            $table->string('no_rek', 100)->unique();
            $table->string('nama_rekening', 255);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rek_manuals');
    }
};
