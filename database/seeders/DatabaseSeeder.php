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
        // Panggil SuperAdminSeeder
        $this->call(SuperAdminSeeder::class);

        // Admin default
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nama'          => 'Admin',
                'jabatan'       => 'Admin',
                'password'      => Hash::make('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'alif@gmail.com'],
            [
                'nama'          => 'Alif',
                'jabatan'       => 'Admin',
                'password'      => Hash::make('password1234'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'asep@gmail.com'],
            [
                'nama'          => 'Asep',
                'jabatan'       => 'Admin',
                'password'      => Hash::make('password1234'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'filah@gmail.com'],
            [
                'nama'          => 'Filah',
                'jabatan'       => 'Admin',
                'password'      => Hash::make('password1234'),
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'hrdchika@gmail.com'],
            [
                'nama'          => 'HRD Chika',
                'jabatan'       => 'HRD',
                'password'      => Hash::make('password1234'),
            ]
        );
    }
}
