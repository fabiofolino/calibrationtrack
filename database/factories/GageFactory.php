<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gage>
 */
class GageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => $this->faker->words(2, true) . ' Gage',
            'serial_number' => strtoupper($this->faker->bothify('??###')),
            'frequency_days' => $this->faker->randomElement([30, 90, 180, 365]),
            'next_due_date' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
        ];
    }
}
