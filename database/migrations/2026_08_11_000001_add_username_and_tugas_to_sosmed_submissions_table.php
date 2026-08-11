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
            $table->string('username_sosmed')->nullable()->after('nama');
            $table->string('pilihan_tugas')->nullable()->after('sosmed_platform');
            $table->text('tugas_link')->nullable()->after('pilihan_tugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sosmed_submissions', function (Blueprint $table) {
            $table->dropColumn(['username_sosmed', 'pilihan_tugas', 'tugas_link']);
        });
    }
};
