<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->enum('reply_penambahan_type', ['text', 'image'])
                ->default('text')
                ->after('reply_penambahan');
            $table->string('reply_penambahan_image')->nullable()->after('reply_penambahan_type');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn(['reply_penambahan_type', 'reply_penambahan_image']);
        });
    }
};
