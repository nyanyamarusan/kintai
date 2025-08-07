<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use App\Models\RequestRest;
use App\Services\IndexDateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        
        return view('attendance', compact('user', 'attendance'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        switch ($request->input('action')) {
            case 'clock_in':
                if (!$attendance) {
                    Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $today,
                        ],
                        [
                            'clock_in' => now()->format('H:i'),
                        ]
                    );
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

        return redirect()->back();
    }

    public function index(Request $request, IndexDateService $indexDateService)
    {
        $user = Auth::user();

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
        }

        return view('index', compact('user', 'attendances',
            'days', 'year', 'month', 'prevMonth', 'nextMonth'));
    }

    public function update(RequestRequest $request)
    {
        $user = Auth::user();

        $requestData = $request->only([
            'attendance_id',
            'clock_in',
            'clock_out',
            'reason',
        ]);
        $requestData['user_id'] = $user->id;
        $attendanceRequest = AttendanceRequest::create($requestData);

        $restInputs = $request->input('rest');

        foreach ($restInputs as $rest) {
            if (!empty($rest['start_time'])) {
                RequestRest::create([
                    'request_id' => $attendanceRequest->id,
                    'start_time' => $rest['start_time'],
                    'end_time' => $rest['end_time'],
                ]);
            }
        }

        return redirect('/stamp_correction_request/list');
    }

    public function showRequests(Request $request)
    {
        $user = Auth::user();
        $tab = request()->get('tab', 'pending');

        if ($tab === 'approved') {
            $requests = AttendanceRequest::where('approved', true)
                ->whereHas('attendance', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('attendance.user')
                ->get();
        } else {
            $requests = AttendanceRequest::where('approved', false)
                ->whereHas('attendance', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('attendance.user')
                ->get();
        }

        foreach ($requests as $req) {
            if ($req->attendance && $req->attendance->date) {
                $req->attendance->date = Carbon::parse($req->attendance->date)->toDateString();
            }
        }

        return view('request', compact('requests', 'tab'));
    }
}
