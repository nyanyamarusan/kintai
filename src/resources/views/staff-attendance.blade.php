@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh">
<div class="container pt-5p col-8">
    <h2 class="fw-bold content-title border-left pl-2p">{{ $user->name }}さんの勤怠</h2>
<div class="p-2">
    <div class="d-flex justify-content-between align-items-center bg-white rounded-10 py-1p px-2p mt-5p">
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15" href="{{ route('staff-attendance.show',
            ['id' => $user->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">
            <img src="{{ asset('arrow.png') }}" class="img-fluid col-3 opacity-30"> 前月
        </a>
        <div class="d-flex justify-content-center align-items-center col-2">
            <img src="{{ asset('calendar.png') }}" class="img-fluid col-2">
            <div class="col-1"></div>
            <p class="m-0 link fw-bold text-black">{{ sprintf('%04d/%02d', $year, $month) }}</p>
        </div>
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15 text-end" href="{{ route('staff-attendance.show',
            ['id' => $user->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">翌月 
            <img src="{{ asset('arrow.png') }}" class="img-fluid rotate-180 col-3 opacity-30">
        </a>
    </div>
    <table class="table rounded-10 mt-5p table-fixed fw-bold no-border">
        <thead>
            <tr class="table-border__th">
                <th class="px-5 text-73">日付</th>
                <th class="text-center text-73">出勤</th>
                <th class="text-center text-73">退勤</th>
                <th class="text-center text-73">休憩</th>
                <th class="text-center text-73">合計</th>
                <th class="px-5 text-73">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
            @php
            $attendance = $attendances[$day->toDateString()] ?? null;
            @endphp
            <tr class="table-border__td">
                <td class="px-5 text-73">
                    {{ $day->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})
                </td>
                <td class="text-center text-73">
                    {{ $attendance?->formatted_clock_in ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $attendance?->formatted_clock_out ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $attendance?->formatted_total_rest ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $attendance?->formatted_total_work ?? '' }}
                </td>
                <td class="px-5">
                    @if ($attendance && $attendance->id)
                    <a href="/attendance/{{ $attendance->id }}" class="text-decoration-none text-black">詳細</a>
                    @else
                    <form action="/attendance/date" method="post">
                        @csrf
                        <input type="hidden" name="date" value="{{ $day->toDateString() }}">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <button type="submit" class="text-decoration-none text-black border-0 bg-transparent p-0 fw-bold">詳細</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <form action="{{ route('export', ['id' => $user->id]) }}" method="get">
        <div class="text-end">
            <button type="submit" class="btn bg-black rounded-2 my-4p text-1vw45 text-white w-18 ls-15 fw-bold">CSV出力</button>
        </div>
    </form>
</div>
@endsection
