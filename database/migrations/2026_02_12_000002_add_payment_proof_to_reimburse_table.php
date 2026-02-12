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
        Schema::table('reimburse', function (Blueprint $table) {
            $table->string('payment_proof_type', 20)->nullable()->after('status');
            $table->text('payment_proof_text')->nullable()->after('payment_proof_type');
            $table->string('payment_proof_image')->nullable()->after('payment_proof_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimburse', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_type', 'payment_proof_text', 'payment_proof_image']);
        });
    }
};
