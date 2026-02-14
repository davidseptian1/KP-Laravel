<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimburse', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('no_rekening');
            $table->string('payment_provider', 50)->nullable()->after('payment_method');
            $table->string('payment_account_number', 50)->nullable()->after('payment_provider');
            $table->string('payment_account_name', 100)->nullable()->after('payment_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('reimburse', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_provider',
                'payment_account_number',
                'payment_account_name',
            ]);
        });
    }
};
