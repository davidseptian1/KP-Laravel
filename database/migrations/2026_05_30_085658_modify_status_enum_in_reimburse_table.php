<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE reimburse MODIFY COLUMN status ENUM('pending', 'waiting_approval_direksi', 'approved', 'rejected', 'revision') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE reimburse MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'revision') DEFAULT 'pending'");
    }
};
