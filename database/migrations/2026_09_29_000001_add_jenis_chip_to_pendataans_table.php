<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendataans', function (Blueprint $table) {
            $table->string('jenis_chip', 20)->nullable()->after('nama_produk')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pendataans', function (Blueprint $table) {
            $table->dropIndex(['jenis_chip']);
            $table->dropColumn('jenis_chip');
        });
    }
};
