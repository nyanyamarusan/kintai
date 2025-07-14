<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function attendance()
    {
        $user = User::find(1);

        //$user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        return view('attendance', compact('user', 'attendance'));
    }

    public function store(Request $request)
    {
        $user = User::find(1);

        //$user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        switch ($request->input('action')) {
            case 'clock_in':
                if (!$attendance) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $today,
                        'clock_in' => now()->format('H:i'),
                    ]);
                }
                break;

            case 'rest_start':
                if ($attendance) {
                    $attendance->restTimes()->create([
                        'start_time' => now()->format('H:i'),
                    ]);
                }
                break;

            case 'rest_end':
                if ($attendance) {
                    $rest = $attendance->restTimes()->whereNull('end_time')->first();
                    if ($rest) {
                        $rest->update([
                            'end_time' => now()->format('H:i'),
                        ]);
                    }
                }
                break;

            case 'clock_out':
                if ($attendance && !$attendance->clock_out) {
                    $attendance->update([
                        'clock_out' => now()->format('H:i'),
                    ]);
                }
                break;
        }

        return redirect('/attendance/list');
    }

    public function index()
    {
        return view('index');
    }

}
