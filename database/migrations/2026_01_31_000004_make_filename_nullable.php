<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        if (Schema::hasTable('imports') && Schema::hasColumn('imports', 'filename')) {
            Schema::table('imports', function (Blueprint $table) {
                // Make filename nullable since we use file_name for the same purpose
                $table->string('filename')->nullable()->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('imports') && Schema::hasColumn('imports', 'filename')) {
            Schema::table('imports', function (Blueprint $table) {
                $table->string('filename')->nullable(false)->change();
            });
        }
    }
};
