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
        Schema::create('data_cut_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('backup_file')->nullable();
            $table->string('backup_size')->nullable();
            $table->date('cut_date')->comment('Tanggal cutoff data yang dihapus');
            $table->integer('transaksis_deleted')->default(0);
            $table->integer('deposits_deleted')->default(0);
            $table->integer('reimburse_deleted')->default(0);
            $table->integer('minusans_deleted')->default(0);
            $table->integer('imports_deleted')->default(0);
            $table->integer('tag_nomor_pasca_bayars_deleted')->default(0);
            $table->integer('tag_pln_internets_deleted')->default(0);
            $table->integer('tag_lainnyas_deleted')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'backing_up', 'deleting', 'completed', 'failed'])->default('pending');
            $table->text('error_log')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_cut_logs');
    }
};
