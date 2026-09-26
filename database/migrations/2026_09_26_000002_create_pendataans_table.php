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
        Schema::create('pendataans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('nama_produk');
            $table->decimal('harga_qty', 15, 2)->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->integer('qty')->default(1);
            $table->string('gambar')->nullable();
            $table->timestamps();

            // Indexes for fast searching and filtering
            $table->index('nama');
            $table->index('nama_produk');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendataans');
    }
};
