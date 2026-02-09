<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        if (! Schema::hasTable('imports')) {
            Schema::create('imports', function (Blueprint $table) {
                $table->id();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
                $table->integer('rows_inserted')->default(0);
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('imports');
    }
};
