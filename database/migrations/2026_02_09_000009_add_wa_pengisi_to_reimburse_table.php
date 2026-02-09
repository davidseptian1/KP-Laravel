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
            $table->string('wa_pengisi', 20)->nullable()->after('wa_penerima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimburse', function (Blueprint $table) {
            $table->dropColumn('wa_pengisi');
        });
    }
};
