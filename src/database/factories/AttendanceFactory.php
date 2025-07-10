<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Attendance::class;

    public function definition(): array
    {
        $in = $this->faker->dateTimeBetween('08:00', '10:00');
        $out = (clone $in)->modify('+' . rand(7, 9) . ' hours');

        return [
            'user_id' => null,
            'date' => $this->faker->date(),
            'clock_in' => $in->format('H:i'),
            'clock_out' => $out->format('H:i'),
            'break_time' => $this->faker->numberBetween(30, 60),
            'work_time' => $this->faker->numberBetween(420, 540),
        ];
    }
}
