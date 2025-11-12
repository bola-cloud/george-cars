<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' ' . $this->faker->word(),
            'serial' => strtoupper($this->faker->bothify('??####')),
            'meta' => [
                'platform' => $this->faker->randomElement(['ios', 'android', 'linux', 'windows']),
                'os_version' => $this->faker->semver(),
            ],
        ];
    }
}
