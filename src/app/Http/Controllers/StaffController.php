<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use App\Models\User;
use App\Services\IndexDateService;
use Carbon\Carbon;
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
                        $attendance->update([
                            'total_rest' => $attendance->total_rest_minutes,
                        ]);
                    }
                }
                break;

            case 'clock_out':
                if ($attendance && !$attendance->clock_out) {
                    $attendance->update([
                        'clock_out' => now()->format('H:i'),
                        'total_work' => $attendance->total_work_minutes,
                    ]);
                }
                break;
        }

        return redirect()->back();
    }

    public function index(Request $request, IndexDateService $indexDateService)
    {
        // $user = Auth::user();
        $user = User::find(1);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $days = $indexDateService->getDaysOfMonth($year, $month);

        $carbon = Carbon::create($year, $month);
        $prevMonth = $carbon->copy()->subMonth();
        $nextMonth = $carbon->copy()->addMonth();

        $attendances = Attendance::with('restTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        foreach ($days as $day) {
            $date = $day->toDateString();
            if (!isset($attendances[$date])) {
                $emptyAttendance = Attendance::firstOrCreate([
                    'user_id' => $user->id,
                    'date' => $date,
                ]);
                $attendances[$date] = $emptyAttendance;
            }
        }

        return view('index', compact('user', 'attendances',
            'days', 'year', 'month', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
    {
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::with('user', 'restTimes', 'request')->findOrFail($id);
        } else {
            //$user = Auth::user();
            $user = User::find(1);
            $attendance = Attendance::with('user', 'restTimes', 'request')
                ->where('user_id', $user->id)
                ->findOrFail($id);
        }

        $user = $attendance->user;

        return view('show', compact('attendance', 'user'));
    }

    public function update(RequestRequest $request)
    {
        //$user = Auth::user();
        $user = User::find(1);

        $requestData = $request->only([
            'attendance_id',
            'clock_in',
            'clock_out',
            'rest',
            'reason',
        ]);

        $requestData['user_id'] = $user->id;

        AttendanceRequest::create($requestData);

        return redirect('/attendance/list');
    }

}
