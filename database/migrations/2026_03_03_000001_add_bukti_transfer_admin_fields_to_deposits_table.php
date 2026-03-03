<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->enum('bukti_transfer_admin_type', ['text', 'image'])
                ->nullable()
                ->after('reply_penambahan_image');
            $table->text('bukti_transfer_admin_text')
                ->nullable()
                ->after('bukti_transfer_admin_type');
            $table->string('bukti_transfer_admin_image')
                ->nullable()
                ->after('bukti_transfer_admin_text');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_transfer_admin_type',
                'bukti_transfer_admin_text',
                'bukti_transfer_admin_image',
            ]);
        });
    }
};
