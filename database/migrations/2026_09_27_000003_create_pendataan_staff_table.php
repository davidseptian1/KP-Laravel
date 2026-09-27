<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pendataan_staff', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('username')->unique();
            $table->string('password'); // 5 huruf random
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial 13 staff accounts with predefined usernames and random 5-letter passwords
        $initialStaff = [
            ['nama' => 'Ginta',  'username' => 'ginta',  'password' => 'myskq'],
            ['nama' => 'Sinta',  'username' => 'sinta',  'password' => 'rjckn'],
            ['nama' => 'Reno',   'username' => 'reno',   'password' => 'hxugk'],
            ['nama' => 'Dira',   'username' => 'dira',   'password' => 'smeiz'],
            ['nama' => 'Pise',   'username' => 'pise',   'password' => 'nyxjp'],
            ['nama' => 'Rani',   'username' => 'rani',   'password' => 'dskai'],
            ['nama' => 'Diah',   'username' => 'diah',   'password' => 'qbjts'],
            ['nama' => 'Diya',   'username' => 'diya',   'password' => 'coznj'],
            ['nama' => 'Rafi',   'username' => 'rafi',   'password' => 'vbkfo'],
            ['nama' => 'Rudi',   'username' => 'rudi',   'password' => 'mvrgd'],
            ['nama' => 'Khodam', 'username' => 'khodam', 'password' => 'nqyuw'],
            ['nama' => 'Nadir',  'username' => 'nadir',  'password' => 'owgux'],
            ['nama' => 'Nuni',   'username' => 'nuni',   'password' => 'dfahi'],
        ];

        $now = now();
        foreach ($initialStaff as $staff) {
            DB::table('pendataan_staff')->updateOrInsert(
                ['username' => $staff['username']],
                [
                    'nama' => $staff['nama'],
                    'password' => $staff['password'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendataan_staff');
    }
};
