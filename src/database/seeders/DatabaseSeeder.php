<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(5)->create();

        Attendance::factory()->count(10)->create()->each(function ($attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            for ($i = 0; $i < rand(1, 3); $i++) {
                $start = (clone $clockIn)->addMinutes(rand(60, 240));
                if ($start->greaterThanOrEqualTo($clockOut)) {
                    break;
                }
                $end = (clone $start)->addMinutes(rand(15, 60));
                if ($end->greaterThan($clockOut)) {
                    $end = (clone $clockOut)->subMinutes(rand(5, 15));
                }
                if ($end->lessThanOrEqualTo($start)) {
                    continue;
                }

                $attendance->restTimes()->create([
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                ]);
            }

            $attendance->refresh();

            $totalRest = $attendance->total_rest_minutes;
            $totalWork = $attendance->total_work_minutes;

            $attendance->update([
                'total_rest' => $totalRest,
                'total_work' => $totalWork,
            ]);
        });

        Admin::factory()->count(2)->create();
    }
}
