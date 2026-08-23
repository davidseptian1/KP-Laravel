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
            $table->string('company_name')->nullable()->after('owner_name');
            $table->string('division')->nullable()->after('company_name');
            $table->string('payment_method')->nullable()->after('division');
            $table->string('cicilan')->default('Tanpa Cicilan')->after('account_name');
            $table->date('po_date')->nullable()->after('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persediaan_stoks', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'division',
                'payment_method',
                'cicilan',
                'po_date'
            ]);
        });
    }
};
