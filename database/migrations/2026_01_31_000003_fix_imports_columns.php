<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        if (Schema::hasTable('imports')) {
            Schema::table('imports', function (Blueprint $table) {
                if (! Schema::hasColumn('imports', 'file_path')) {
                    $table->string('file_path')->after('id')->nullable();
                }
                if (! Schema::hasColumn('imports', 'file_name')) {
                    $table->string('file_name')->nullable()->after('file_path');
                }
                if (! Schema::hasColumn('imports', 'status')) {
                    $table->enum('status', ['pending','completed','failed'])->default('pending')->after('file_name');
                }
                if (! Schema::hasColumn('imports', 'rows_inserted')) {
                    $table->integer('rows_inserted')->default(0)->after('status');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('imports')) {
            Schema::table('imports', function (Blueprint $table) {
                if (Schema::hasColumn('imports', 'file_path')) {
                    $table->dropColumn('file_path');
                }
                if (Schema::hasColumn('imports', 'file_name')) {
                    $table->dropColumn('file_name');
                }
                if (Schema::hasColumn('imports', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('imports', 'rows_inserted')) {
                    $table->dropColumn('rows_inserted');
                }
            });
        }
    }
};
