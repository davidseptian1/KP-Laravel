<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_lainnya_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_lainnya_id')->constrained('tag_lainnyas')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('tagihan', 15, 2)->nullable();
            $table->dateTime('tanggal_payment')->nullable();
            $table->timestamps();
            $table->unique(['tag_lainnya_id', 'periode_bulan', 'periode_tahun'], 'tag_lainnya_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_lainnya_periods');
    }
};
