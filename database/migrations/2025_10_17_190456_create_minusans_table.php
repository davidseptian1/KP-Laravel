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
        Schema::create('minusans', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->date('tanggal');
            $table->enum('server', ['CMP', 'CCN', 'CWN']);
            $table->string('nama', 255);
            $table->enum('spl', ['IFG7', 'CCN', 'HB51']);
            $table->enum('produk', ['IFG77', 'DV09', 'MCE12']);
            $table->integer('nomor');
            $table->decimal('total', 8, 2);
            $table->integer('qty');
            $table->decimal('total_per_org', 8, 2);
            $table->enum('keterangan', ['Dialihkan', 'Digagalkan', 'Gagal']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('minusans');
    }
};
