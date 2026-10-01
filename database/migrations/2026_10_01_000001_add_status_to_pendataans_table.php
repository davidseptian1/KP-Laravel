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
        if (Schema::hasTable('pendataans') && !Schema::hasColumn('pendataans', 'status')) {
            Schema::table('pendataans', function (Blueprint $table) {
                $table->enum('status', ['pending', 'sukses', 'gagal'])->default('pending')->after('gambar')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pendataans') && Schema::hasColumn('pendataans', 'status')) {
            Schema::table('pendataans', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
