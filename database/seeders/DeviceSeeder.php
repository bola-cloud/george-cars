<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some users with devices
        User::factory()
            ->count(5)
            ->has(Device::factory()->count(\random_int(1, 4)))
            ->create();

        // Create a known user for API testing
        $user = User::factory()->create([
            'name' => 'API Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // password
            'phone' => '1234567890',
            'ip' => '127.0.0.1',
        ]);

        Device::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);
    }
}
