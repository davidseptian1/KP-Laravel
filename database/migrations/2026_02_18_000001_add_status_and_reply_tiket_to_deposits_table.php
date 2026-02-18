<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->text('reply_tiket')->nullable()->after('nama_rekening');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('reply_penambahan');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn(['reply_tiket', 'status']);
        });
    }
};
