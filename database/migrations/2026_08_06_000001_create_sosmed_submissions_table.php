<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sosmed_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_first_word')->index();
            $table->string('divisi');
            $table->string('sosmed_platform');
            $table->json('photos');
            $table->string('status')->default('pending')->index();
            $table->text('catatan')->nullable();
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sosmed_submissions');
    }
};
