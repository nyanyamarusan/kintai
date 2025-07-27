@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh">
<div class="container pt-5p col-8">
    <h2 class="fw-bold content-title border-left pl-2p">
        {{ $dates['current']->translatedFormat('Y年n月j日') }}の勤怠
    </h2>
<div class="p-2">
    <div class="d-flex justify-content-between align-items-center bg-white rounded-10 py-1p px-2p mt-5p">
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15" href="{{ route('admin-index',
            ['year' => $prevDay->year, 'month' => $prevDay->month, 'day' => $prevDay->day]) }}">
            <img src="{{ asset('arrow.png') }}" class="img-fluid col-3 opacity-30"> 前日
        </a>
        <div class="d-flex justify-content-center align-items-center col-2">
            <img src="{{ asset('calendar.png') }}" class="img-fluid col-2">
            <div class="col-1"></div>
            <p class="m-0 link fw-bold text-black">{{ sprintf('%04d/%02d/%02d', $year, $month, $day) }}</p>
        </div>
        <a class="text-decoration-none col-1 text-1vw05 text-73 ls-15 text-end" href="{{ route('admin-index',
            ['year' => $nextDay->year, 'month' => $nextDay->month, 'day' => $nextDay->day]) }}">翌日 
            <img src="{{ asset('arrow.png') }}" class="img-fluid rotate-180 col-3 opacity-30">
        </a>
    </div>
    <table class="table rounded-10 mt-5p table-fixed fw-bold no-border">
        <thead>
            <tr class="table-border__th">
                <th class="text-73 text-center">名前</th>
                <th class="text-center text-73">出勤</th>
                <th class="text-center text-73">退勤</th>
                <th class="text-center text-73">休憩</th>
                <th class="text-center text-73">合計</th>
                <th class="px-5 text-73">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceList as $data)
            <tr class="table-border__td">
                <td class="text-73 text-center">
                    {{ $data['user']?->name }}
                </td>
                <td class="text-center text-73">
                    {{ $data['attendance']?->formatted_clock_in ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $data['attendance']?->formatted_clock_out ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $data['attendance']?->formatted_total_rest ?? '' }}
                </td>
                <td class="text-center text-73">
                    {{ $data['attendance']?->formatted_total_work ?? '' }}
                </td>
                <td class="px-5">
                    @if ($data['attendance'] && $data['attendance']->id)
                    <a href="/attendance/{{ $data['attendance']->id }}" class="text-decoration-none text-black">詳細</a>
                    @else
                    <form action="/attendance/date" method="post">
                        @csrf
                        <input type="hidden" name="date" value="{{ $dates['current']->toDateString() }}">
                        <input type="hidden" name="user_id" value="{{ $data['user']?->id }}">
                        <button type="submit" class="text-decoration-none text-black border-0 bg-transparent p-0 fw-bold">詳細</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
