<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // Admin default
        User::create([
            'nama'          => 'Admin',
            'email'         => 'admin@gmail.com',
            'jabatan'       => 'Admin',
            'password'      => Hash::make('admin123'),
        ]);

        User::create([
            'nama'          => 'Alif',
            'email'         => 'alif@gmail.com',
            'jabatan'       => 'Admin',
            'password'      => Hash::make('password1234'),
        ]);

        User::create([
            'nama'          => 'Asep',
            'email'         => 'asep@gmail.com',
            'jabatan'       => 'Admin',
            'password'      => Hash::make('password1234'),
        ]);

        User::create([
            'nama'          => 'Filah',
            'email'         => 'filah@gmail.com',
            'jabatan'       => 'Admin',
            'password'      => Hash::make('password1234'),
        ]);
        
        User::create([
            'nama'          => 'HRD Chika',
            'email'         => 'hrdchika@gmail.com',
            'jabatan'       => 'HRD',
            'password'      => Hash::make('password1234'),
        ]);

    }
}
