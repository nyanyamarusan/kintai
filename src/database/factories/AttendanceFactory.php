<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
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
        $in = $this->faker->dateTimeBetween('today 08:00', 'today 10:00');
        $out = (clone $in)->modify('+' . rand(7, 9) . ' hours');

        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'date' => $in->format('Y-m-d'),
            'clock_in' => $in->format('H:i'),
            'clock_out' => $out->format('H:i'),
            'break_time' => 0,
            'work_time' => 0,
        ];
    }
}
