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
        if (Schema::hasTable('pendataans')) {
            Schema::table('pendataans', function (Blueprint $table) {
                if (!Schema::hasColumn('pendataans', 'is_susulan')) {
                    $table->boolean('is_susulan')->default(false)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pendataans')) {
            Schema::table('pendataans', function (Blueprint $table) {
                if (Schema::hasColumn('pendataans', 'is_susulan')) {
                    $table->dropColumn('is_susulan');
                }
            });
        }
    }
};
