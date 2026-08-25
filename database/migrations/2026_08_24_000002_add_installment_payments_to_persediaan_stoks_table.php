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
            $table->json('installment_payments')->nullable()->after('goods_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persediaan_stoks', function (Blueprint $table) {
            $table->dropColumn('installment_payments');
        });
    }
};
