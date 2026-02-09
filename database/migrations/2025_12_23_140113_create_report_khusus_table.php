<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('report_khusus', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama', 100);
            $table->string('produk', 100);
            $table->decimal('total', 15, 2);
            $table->string('server', 50);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_khusus');
    }
};
