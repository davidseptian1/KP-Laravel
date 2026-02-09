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
        Schema::table('reimburse', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('user_id');
            $table->string('divisi', 50)->nullable()->after('nama');
            $table->string('nama_barang')->nullable()->after('nominal');
            $table->text('keperluan')->nullable()->after('keterangan');
            $table->json('bukti_files')->nullable()->after('bukti_file');
            $table->string('wa_penerima', 20)->nullable()->after('bukti_files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimburse', function (Blueprint $table) {
            $table->dropColumn(['nama', 'divisi', 'nama_barang', 'keperluan', 'bukti_files', 'wa_penerima']);
        });
    }
};
