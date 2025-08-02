<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function show($id)
    {
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::with('user', 'restTimes', 'requests')
                ->findOrFail($id);
            $user = $attendance->user;
        } else {
            $user = Auth::user();
            $attendance = Attendance::with('user', 'restTimes', 'requests')
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $user = $attendance->user;
        }

        return view('show', compact('attendance', 'user'));
    }

    public function redirectByDate(Request $request)
    {
        $date = $request->input('date');

        if (Auth::guard('admin')->check()) {
            $userId = $request->input('user_id');
            $user = User::find($userId);
        } else {
            $user = Auth::user();
        }

        $parsedDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date' => $parsedDate
            ],
            [
                'clock_in' => null,
                'clock_out' => null,
                'reason' => null,
            ]
        );

        return redirect('/attendance/' . $attendance->id);
    }
}
