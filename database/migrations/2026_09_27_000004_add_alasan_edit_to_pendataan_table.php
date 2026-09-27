<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendataans', function (Blueprint $table) {
            // Mandatory reason text when editing a record.
            // nullable for existing rows; enforced at app level on new edits.
            $table->text('alasan_edit')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('pendataans', function (Blueprint $table) {
            $table->dropColumn('alasan_edit');
        });
    }
};
