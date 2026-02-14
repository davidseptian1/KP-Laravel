<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_supplier');
            $table->decimal('nominal', 15, 2);
            $table->string('bank');
            $table->string('server');
            $table->string('no_rek');
            $table->string('nama_rekening');
            $table->text('reply_penambahan')->nullable();
            $table->time('jam');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
