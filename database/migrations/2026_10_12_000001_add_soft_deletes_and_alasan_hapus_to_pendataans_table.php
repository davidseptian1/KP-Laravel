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
                if (!Schema::hasColumn('pendataans', 'alasan_hapus')) {
                    $table->text('alasan_hapus')->nullable()->after('alasan_edit');
                }
                if (!Schema::hasColumn('pendataans', 'deleted_by')) {
                    $table->string('deleted_by')->nullable()->after('alasan_hapus');
                }
                if (!Schema::hasColumn('pendataans', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
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
                if (Schema::hasColumn('pendataans', 'alasan_hapus')) {
                    $table->dropColumn('alasan_hapus');
                }
                if (Schema::hasColumn('pendataans', 'deleted_by')) {
                    $table->dropColumn('deleted_by');
                }
                if (Schema::hasColumn('pendataans', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
