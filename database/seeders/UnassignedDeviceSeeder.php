<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class UnassignedDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 devices with no assigned user (admin created)
        Device::factory()->count(20)->create([
            'user_id' => null,
        ]);
    }
}
