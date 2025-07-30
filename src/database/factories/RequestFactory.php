<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::createFromTime(rand(7, 11), rand(0, 59));
        $end = (clone $start)->addHours(rand(8, 10));

        return [
            'attendance_id' => Attendance::factory(),
            'clock_in' => $start->format('H:i'),
            'clock_out' => $end->format('H:i'),
            'reason' => $this->faker->sentence,
        ];
    }
}
