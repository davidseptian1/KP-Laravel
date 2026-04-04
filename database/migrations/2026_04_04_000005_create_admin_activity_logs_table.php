<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 120)->nullable();
            $table->string('actor_role', 30)->nullable();
            $table->string('method', 10);
            $table->string('route_name', 180)->nullable();
            $table->string('path', 255);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->json('request_data')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'id']);
            $table->index(['actor_role', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['method', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index('route_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
