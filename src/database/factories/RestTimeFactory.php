<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestTime>
 */
class RestTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $attendance = Attendance::inRandomOrder()->first() ?? Attendance::factory()->create();

        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out)->subMinutes(5);

        $start = Carbon::instance(fake()->dateTimeBetween($clockIn->toDateTimeString(), $clockOut->toDateTimeString()))->seconds(0);
        $end = $start->copy()->addMinutes(rand(5, 60))->seconds(0);

        if ($end > $clockOut) {
            $end = $clockOut->copy()->seconds(0);
        }

        return [
            'attendance_id' => $attendance->id,
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
        ];
    }
}
