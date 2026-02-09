<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    public function up()
    {
        if (Schema::hasTable('imports') && Schema::hasColumn('imports', 'status')) {
            // Expand enum to include 'pending' for backward compatibility
            // MySQL syntax to modify enum values
            DB::statement("ALTER TABLE imports MODIFY COLUMN status ENUM('pending', 'queued', 'processing', 'done', 'failed') NOT NULL DEFAULT 'queued'");
        }
    }

    public function down()
    {
        if (Schema::hasTable('imports') && Schema::hasColumn('imports', 'status')) {
            // Revert to previous enum without 'pending'
            DB::statement("ALTER TABLE imports MODIFY COLUMN status ENUM('queued', 'processing', 'done', 'failed') NOT NULL DEFAULT 'queued'");
        }
    }
};
