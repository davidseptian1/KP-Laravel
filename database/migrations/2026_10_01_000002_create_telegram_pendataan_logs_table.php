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
        if (!Schema::hasTable('telegram_pendataan_logs')) {
            Schema::create('telegram_pendataan_logs', function (Blueprint $table) {
                $table->id();
                $table->string('chat_id')->nullable()->index();
                $table->string('chat_title')->nullable();
                $table->bigInteger('message_id')->nullable();
                $table->string('sender_username')->nullable();
                $table->string('sender_name')->nullable();
                $table->text('raw_message')->nullable();
                $table->string('parsed_product')->nullable();
                $table->decimal('parsed_nominal', 15, 2)->nullable();
                $table->string('raw_nominal')->nullable();
                $table->string('status', 30)->default('unmatched')->index(); // matched, unmatched, invalid_format, error
                $table->unsignedBigInteger('pendataan_id')->nullable()->index();
                $table->text('action_note')->nullable();
                $table->boolean('bot_replied')->default(false);
                $table->text('bot_reply_text')->nullable();
                $table->timestamps();

                $table->foreign('pendataan_id')
                    ->references('id')
                    ->on('pendataans')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_pendataan_logs');
    }
};
