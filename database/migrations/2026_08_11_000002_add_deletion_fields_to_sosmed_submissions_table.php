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
        Schema::table('sosmed_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('deleted_by')->nullable()->after('processed_at');
            $table->timestamp('deleted_at')->nullable()->after('deleted_by');

            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sosmed_submissions', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });
    }
};
