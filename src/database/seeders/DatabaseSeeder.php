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

        Attendance::factory()->count(10)->make()->each(function ($attendance) {
            $createdAttendance = Attendance::firstOrCreate(
                [
                    'user_id' => $attendance->user_id,
                    'date' => $attendance->date,
                ],
                [
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                ]
            );
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

                $createdAttendance->restTimes()->create([
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                ]);
            }
        });

        Admin::factory()->count(1)->create();
    }
}
