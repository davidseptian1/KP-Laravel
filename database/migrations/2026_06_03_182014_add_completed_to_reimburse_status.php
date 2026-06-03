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
        // 1. Tambahkan status 'completed' ke tipe ENUM
        DB::statement("ALTER TABLE reimburse MODIFY COLUMN status ENUM('pending', 'waiting_approval_direksi', 'approved', 'rejected', 'revision', 'completed') DEFAULT 'pending'");

        // 2. Data backfill: Ubah data yang sudah ada isinya di catatan_admin (dan saat ini statusnya approved) menjadi completed
        DB::table('reimburse')
            ->where('status', 'approved')
            ->whereNotNull('catatan_admin')
            ->where('catatan_admin', '!=', '')
            ->update(['status' => 'completed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rollback status 'completed' menjadi 'approved'
        DB::table('reimburse')
            ->where('status', 'completed')
            ->update(['status' => 'approved']);

        // 2. Kembalikan tipe ENUM seperti semula
        DB::statement("ALTER TABLE reimburse MODIFY COLUMN status ENUM('pending', 'waiting_approval_direksi', 'approved', 'rejected', 'revision') DEFAULT 'pending'");
    }
};
