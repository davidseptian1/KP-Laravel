<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100);
            $table->unsignedBigInteger('reference_id');
            $table->string('item_code', 100)->nullable();
            $table->text('reason');
            $table->foreignId('deleted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deleted_by_name', 100)->nullable();
            $table->string('deleted_by_role', 50)->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['module', 'reference_id']);
            $table->index('deleted_by_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_logs');
    }
};
