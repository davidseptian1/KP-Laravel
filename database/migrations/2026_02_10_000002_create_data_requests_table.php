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
        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('data_request_forms')->nullOnDelete();
            $table->string('kode_pengajuan', 50)->unique();
            $table->dateTime('tanggal_pengajuan');
            $table->string('aplikasi', 50);
            $table->string('username_akun');
            $table->string('nomor_hp', 20);
            $table->string('email_lama');
            $table->string('email_baru')->nullable();
            $table->string('nama_pemohon');
            $table->text('riwayat_transaksi');
            $table->decimal('saldo_terakhir', 15, 2);
            $table->string('jenis_perubahan', 50);
            $table->text('alasan_perubahan');
            $table->string('foto_ktp');
            $table->string('foto_selfie');
            $table->string('wa_penerima', 20)->nullable();
            $table->string('wa_pengisi', 20)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_requests');
    }
};
