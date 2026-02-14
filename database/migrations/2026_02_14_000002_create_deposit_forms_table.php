<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_forms', function (Blueprint $table) {
            $table->id();
            $table->string('kode_form', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_forms');
    }
};
