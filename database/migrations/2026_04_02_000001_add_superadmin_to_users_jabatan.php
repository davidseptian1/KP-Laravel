<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Superadmin to the jabatan enum
        DB::statement("ALTER TABLE users MODIFY jabatan ENUM('Admin','Staff','HRD','Superadmin') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY jabatan ENUM('Admin','Staff','HRD') NOT NULL");
    }
};
