<?php

namespace Database\Factories;

use App\Models\RestTime;
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

    protected $model = RestTime::class;

    public function definition(): array
    {
        $start = $this->faker->time('H:i:s', '14:00:00');
        $end = Carbon::parse($start)->addMinutes(rand(15, 60))->format('H:i:s');

        return [
            'attendance_id' => null,
            'start_time' => $start,
            'end_time' => $end,
        ];
    }
}
