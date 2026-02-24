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
        if (Schema::hasColumn('minusans', 'total_per_org')) {
            Schema::table('minusans', function (Blueprint $table) {
                $table->dropColumn('total_per_org');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('minusans', 'total_per_org')) {
            Schema::table('minusans', function (Blueprint $table) {
                $table->decimal('total_per_org', 8, 2)->nullable();
            });
        }
    }
};
