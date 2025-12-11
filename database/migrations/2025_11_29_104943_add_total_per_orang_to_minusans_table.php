<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('minusans', function (Blueprint $table) {
            $table->decimal('total_per_orang', 15, 2)->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('minusans', function (Blueprint $table) {
            $table->dropColumn('total_per_orang');
        });
    }
};
