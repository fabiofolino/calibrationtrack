<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalibrationRecord>
 */
class CalibrationRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'measured_value' => $this->faker->randomFloat(4, 0, 100),
            'calibrated_value' => $this->faker->randomFloat(4, 0, 100),
            'calibrated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
