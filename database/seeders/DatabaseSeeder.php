<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DeviceSeeder;
use Database\Seeders\UnassignedDeviceSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin first
        $this->call(AdminUserSeeder::class);

        // Run device seeder (creates users and devices)
        $this->call(DeviceSeeder::class);
        // Seed unassigned devices so clients can claim by serial
        $this->call(UnassignedDeviceSeeder::class);
    }
}
