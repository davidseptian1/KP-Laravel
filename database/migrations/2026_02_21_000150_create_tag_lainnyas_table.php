<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_lainnyas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_rekening_va', 100)->unique();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->string('bank')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_lainnyas');
    }
};
