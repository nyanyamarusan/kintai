<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\RestTime;
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
        Attendance::factory()->count(10)->create();
        RestTime::factory()->count(20)->create();

        foreach (Attendance::all() as $attendance) {
            try {
                $clockIn = Carbon::createFromFormat('H:i:s', $attendance->clock_in);
                $clockOut = Carbon::createFromFormat('H:i:s', $attendance->clock_out);
        
                if ($clockOut->lessThan($clockIn)) {
                    $clockOut->addDay();
                }
        
                $workMinutes = $clockOut->diffInMinutes($clockIn);
        
                $totalRest = 0;
                foreach ($attendance->restTimes as $rest) {
                    try {
                        $start = Carbon::createFromFormat('H:i:s', $rest->start_time);
                        $end = Carbon::createFromFormat('H:i:s', $rest->end_time);
        
                        if ($end->lessThan($start)) {
                            $end->addDay();
                        }
        
                        $restDuration = $end->diffInMinutes($start);
        
                        // 念のため負の値は加算しない
                        if ($restDuration > 0) {
                            $totalRest += $restDuration;
                        }
                    } catch (\Exception $e) {
                        echo "休憩時間の処理でエラー: {$e->getMessage()}\n";
                    }
                }
        
                $actualWork = max($workMinutes - $totalRest, 0);
        
                echo "ID: {$attendance->id}, 出勤: {$clockIn->format('H:i')}, 退勤: {$clockOut->format('H:i')} => 勤務時間: {$workMinutes} 分, 休憩合計: {$totalRest} 分, 実労働: {$actualWork} 分\n";
        
                $attendance->update([
                    'break_time' => $totalRest,
                    'work_time' => $actualWork,
                ]);
        
            } catch (\Exception $e) {
                echo "勤務時間の処理でエラー: {$e->getMessage()}\n";
            }
        }
    
        Admin::factory()->count(2)->create();
    }
}
