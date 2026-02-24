<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE minusans MODIFY server VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY spl VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY produk VARCHAR(100) NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY keterangan VARCHAR(255) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE minusans MODIFY server ENUM('CMP','CCN','CWN') NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY spl ENUM('IFG7','CCN','HB51') NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY produk ENUM('IFG77','DV09','MCE12') NOT NULL");
        DB::statement("ALTER TABLE minusans MODIFY keterangan ENUM('Dialihkan','Digagalkan','Gagal') NOT NULL");
    }
};
