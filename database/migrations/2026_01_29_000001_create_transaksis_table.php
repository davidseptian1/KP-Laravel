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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->unique()->default(DB::raw('(UUID())'));
            $table->dateTime('tgl_entri');
            $table->string('kode_produk', 50);
            $table->string('nomor_tujuan', 50);
            $table->enum('status', ['Gagal', 'Sukses', 'Proses']);
            $table->string('sn', 100)->nullable();
            $table->string('kode_reseller', 50)->nullable();
            $table->string('nama_reseller', 255)->nullable();
            $table->string('modul', 255)->nullable();
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('laba', 12, 2);
            $table->integer('durasi_detik')->nullable(); // Kecepatan transaksi dalam detik
            $table->timestamps();
            
            // Index untuk performa query
            $table->index('tgl_entri');
            $table->index('kode_produk');
            $table->index('status');
            $table->index('kode_reseller');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
