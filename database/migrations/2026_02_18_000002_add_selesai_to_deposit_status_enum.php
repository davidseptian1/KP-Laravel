<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE deposits MODIFY status ENUM('pending','approved','rejected','selesai') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE deposits MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
