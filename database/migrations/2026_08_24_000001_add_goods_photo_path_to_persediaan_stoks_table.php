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
        Schema::table('persediaan_stoks', function (Blueprint $table) {
            $table->string('goods_photo_path')->nullable()->after('invoice_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persediaan_stoks', function (Blueprint $table) {
            $table->dropColumn('goods_photo_path');
        });
    }
};
