@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh">
<div class="container pt-5p col-8">
    <h2 class="fw-bold content-title border-left pl-2p">勤怠一覧</h2>
@php
$prevMonth = \Carbon\Carbon::create($year, $month)->subMonth();
$nextMonth = \Carbon\Carbon::create($year, $month)->addMonth();
@endphp
<div class="p-2">
    <div class="d-flex justify-content-between align-items-center bg-white rounded-10 py-1p px-2p mt-5p">
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15" href="{{ route('index',
            ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">
            <img src="{{ asset('arrow.png') }}" class="img-fluid col-3 opacity-30"> 前月
        </a>
        <div class="d-flex justify-content-center align-items-center col-2">
            <img src="{{ asset('calendar.png') }}" class="img-fluid col-2">
            <div class="col-1"></div>
            <p class="m-0 link fw-bold text-black">{{ sprintf('%04d/%02d', $year, $month) }}</p>
        </div>
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15 text-end" href="{{ route('index',
            ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">翌月 
            <img src="{{ asset('arrow.png') }}" class="img-fluid rotate-180 col-3 opacity-30">
        </a>
    </div>
    <table class="table rounded-10 mt-5p table-fixed fw-bold">
        <thead>
            <tr class="table-border">
                <th class="px-5 text-73">日付</th>
                <th class="px-5 text-center text-73">出勤</th>
                <th class="px-5 text-center text-73">退勤</th>
                <th class="px-5 text-center text-73">休憩</th>
                <th class="px-5 text-center text-73">合計</th>
                <th class="px-5 text-73">詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
            function minutesToTime($minutes) {
                if (is_null($minutes)) return '';
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
                return $hours . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
            }
            @endphp

            @foreach ($days as $day)
            @php
            $attendance = $attendances[$day->toDateString()] ?? null;
            @endphp
            <tr>
                <td class="px-5 text-73">
                    {{ $day->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})
                </td>
                <td class="px-5 text-center text-73">
                    {{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                </td>
                <td class="px-5 text-center text-73">
                    {{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                </td>
                <td class="px-5 text-center text-73">
                    {{ $attendance?->break_time ? minutesToTime($attendance->break_time) : '' }}
                </td>
                <td class="px-5 text-center text-73">
                    {{ $attendance?->work_time ? minutesToTime($attendance->work_time) : '' }}
                </td>
                <td class="px-5">
                    <a href="/attendance/{{ $user->id }}" class="text-decoration-none text-black">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
