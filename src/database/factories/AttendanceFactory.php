<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
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
        $start = Carbon::createFromTime(rand(8, 10), rand(0, 59));
        $end = (clone $start)->addHours(rand(8, 10));

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'date' => $start->toDateString(),
            'clock_in' => $start->format('H:i'),
            'clock_out' => $end->format('H:i'),
            'total_rest' => 0,
            'total_work' => 0,
        ];
    }
}
