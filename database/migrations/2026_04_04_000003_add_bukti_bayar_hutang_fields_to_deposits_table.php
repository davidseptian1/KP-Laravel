<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->enum('bukti_bayar_hutang_type', ['text', 'image'])
                ->default('text')
                ->after('bukti_transfer_admin_image');
            $table->text('bukti_bayar_hutang_text')
                ->nullable()
                ->after('bukti_bayar_hutang_type');
            $table->string('bukti_bayar_hutang_image')
                ->nullable()
                ->after('bukti_bayar_hutang_text');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_bayar_hutang_type',
                'bukti_bayar_hutang_text',
                'bukti_bayar_hutang_image',
            ]);
        });
    }
};
