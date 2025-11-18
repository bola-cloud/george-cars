<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default admin user if it doesn't exist
        $email = 'admin@example.com';

        $user = User::where('email', $email)->first();
        if (! $user) {
            User::create([
                'name' => 'Administrator',
                'email' => $email,
                'password' => Hash::make('password'),
                'phone' => '0000000000',
                'is_admin' => true,
            ]);
        } else {
            $user->update(['is_admin' => true]);
        }
    }
}
