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
        if (!Schema::hasTable('telegram_webhook_raw_logs')) {
            Schema::create('telegram_webhook_raw_logs', function (Blueprint $table) {
                $table->id();
                $table->string('source', 50)->default('telegram'); // telegram, direct_sms, simulated
                $table->string('ip_address', 45)->nullable();
                $table->string('http_method', 10)->default('POST');
                $table->bigInteger('update_id')->nullable()->index();
                $table->string('update_type', 50)->nullable()->index(); // message, channel_post, edited_message, my_chat_member, etc.
                $table->string('chat_id')->nullable()->index();
                $table->string('chat_title')->nullable();
                $table->string('sender_name')->nullable();
                $table->text('summary')->nullable();
                $table->longText('raw_payload')->nullable();
                $table->string('status', 40)->default('received')->index(); // received, matched, unmatched, invalid_format, ignored, error
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_webhook_raw_logs');
    }
};
