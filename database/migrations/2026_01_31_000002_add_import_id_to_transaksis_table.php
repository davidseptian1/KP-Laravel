<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        if (! Schema::hasColumn('transaksis', 'import_id')) {
            Schema::table('transaksis', function (Blueprint $table) {
                $table->unsignedBigInteger('import_id')->nullable()->after('id');
                $table->foreign('import_id')->references('id')->on('imports')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
