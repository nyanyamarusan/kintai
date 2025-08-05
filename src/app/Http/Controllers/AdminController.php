<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use App\Models\User;
use App\Services\IndexDateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function index(Request $request, IndexDateService $indexDateService)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $day = $request->input('day', now()->day);

        $dates = $indexDateService->getPreviousCurrentNextDate($year, $month, $day);

        $carbon = $dates['current'];
        $currentDate = $carbon->toDateString();
        $prevDay = $carbon->copy()->subDay();
        $nextDay = $carbon->copy()->addDay();

        $attendances = Attendance::with('user', 'restTimes')
            ->whereDate('date', $currentDate)
            ->get()
            ->keyBy('user_id');

        $users = User::all();

        $attendanceList = $users->map(function ($user) use ($attendances) {
            return [
                'user' => $user,
                'attendance' => $attendances[$user->id] ?? null,
            ];
        });

        return view('admin-index', compact('attendanceList',
            'dates', 'year', 'month', 'day', 'prevDay', 'nextDay', 'users'));
    }

    public function showRequests(Request $request)
    {
        $tab = request()->get('tab', 'pending');

        if ($tab === 'approved') {
            $requests = AttendanceRequest::where('approved', true)
                ->with('attendance.user')->get();
        } else {
            $requests = AttendanceRequest::where('approved', false)
                ->with('attendance.user')->get();
        }

        return view('request', compact('requests', 'tab'));
    }

    public function update(RequestRequest $request)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($request->attendance_id);

        $attendanceData = $request->only([
            'clock_in',
            'clock_out',
            'reason',
        ]);

        $attendance->update($attendanceData);

        $attendance->restTimes()->delete();
        $restInputs = $request->input('rest', []);

        foreach ($restInputs as $rest) {
            if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                $attendance->restTimes()->create([
                    'start_time' => $rest['start_time'],
                    'end_time' => $rest['end_time'],
                ]);
            }
        }

        return redirect('admin/attendance/list');
    }

    public function showStaffs()
    {
        $users = User::all();

        return view('staff', compact('users'));
    }

    public function show(Request $request, IndexDateService $indexDateService, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $days = $indexDateService->getDaysOfMonth($year, $month);

        $carbon = Carbon::create($year, $month);
        $prevMonth = $carbon->copy()->subMonth();
        $nextMonth = $carbon->copy()->addMonth();

        $attendances = Attendance::with('user', 'restTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        foreach ($days as $day) {
            $date = $day->toDateString();
        }

        return view('staff-attendance', compact('user', 'attendances',
            'days', 'year', 'month', 'prevMonth', 'nextMonth'));
    }

    public function export(Request $request, IndexDateService $indexDateService, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $days = $indexDateService->getDaysOfMonth($year, $month);

        $attendances = Attendance::with('user', 'restTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$days->first()->toDateString(), $days->last()->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        $rows = collect();

        foreach ($days as $day) {
            $date = $day->toDateString();
            $attendance = $attendances->get($date);

            if (!$attendance) {
                $attendance = new Attendance([
                    'date' => $date,
                    'clock_in' => null,
                    'clock_out' => null,
                    'formatted_total_rest' => null,
                    'formatted_total_work' => null,
                ]);
            }

            $rows->push($attendance);
        }

        return Excel::download(
            new AttendanceExport($rows, $user),
            'attendance_'.$user->name.'_'.$year.'_'.str_pad($month, 2, '0', STR_PAD_LEFT).'.csv');
    }

    public function approveForm($id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance.user', 'requestRests')
            ->findOrFail($id);
        $user = $attendanceRequest->attendance->user;

        return view('approve', compact('attendanceRequest', 'user'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $attendanceRequest = AttendanceRequest::with('attendance.restTimes', 'requestRests')
                ->findOrFail($id);

            $attendanceRequest->update([
                'approved' => true,
            ]);

            $attendanceRequest->attendance->update([
                'clock_in' => $attendanceRequest->clock_in,
                'clock_out' => $attendanceRequest->clock_out,
            ]);

            $attendance = $attendanceRequest->attendance;
            $attendance->restTimes()->delete();

            foreach ($attendanceRequest->requestRests as $requestRest) {
                $attendance->restTimes()->create([
                    'start_time' => $requestRest->start_time,
                    'end_time' => $requestRest->end_time,
                ]);
            }
        });

        return redirect()->back();
    }
}
