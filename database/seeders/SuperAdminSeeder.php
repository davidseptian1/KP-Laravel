<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a Superadmin account if not exists.
     *
     * Configurable via environment variables:
     * - SUPERADMIN_NAME
     * - SUPERADMIN_EMAIL
     * - SUPERADMIN_PASSWORD
     */
    public function run()
    {
        $name = env('SUPERADMIN_NAME', 'Super Admin');
        $email = env('SUPERADMIN_EMAIL', 'superadmin@example.com');
        $password = env('SUPERADMIN_PASSWORD', 'password123');

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->command->info("Superadmin with email {$email} already exists.");
            // ensure role is Superadmin
            if ($user->jabatan !== 'Superadmin') {
                $user->jabatan = 'Superadmin';
                $user->save();
                $this->command->info('Updated existing user to Superadmin.');
            }
            return;
        }

        User::create([
            'nama' => $name,
            'email' => $email,
            'jabatan' => 'Superadmin',
            'password' => Hash::make($password),
        ]);

        $this->command->info("Superadmin created: {$email} (password: {$password})");
    }
}
