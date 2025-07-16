@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 d-flex justify-content-center align-items-center m-h-100vh">
    <div class="inter fw-bold text-center col-6 d-flex flex-column align-items-center">
        <span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">{{ $attendance->status ?? '勤務外' }}
        </span>

        @php
        \Carbon\Carbon::setLocale('ja');
        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->isoFormat('ddd');
        @endphp

        <p class="fw-normal date mt-5p">{{ $now->year }}年{{ $now->month }}月{{ $now->day }}日({{ $dayOfWeek }})</p>
        <p class="time">{{ $now->format('H:i') }}</p>

        @php
        $status = $attendance->status ?? '勤務外';
        @endphp

        @if ($status === '勤務外')
        <form action="/attendance/list" method="post" class="col-6">
            @csrf
            <input type="hidden" name="action" value="clock_in">
            <button type="submit" class="btn bg-black rounded-20 mt-10p py-4p
                attendance-btn-text text-white col-7 ls-15">
                出勤
            </button>
        </form>
        @endif

        @if ($status === '出勤中' && !$attendance->clock_out)
        <div class="d-flex w-100">
            <form action="/attendance/list" method="post" class="col-6 text-end">
                @csrf
                <input type="hidden" name="action" value="clock_out">
                <button type="submit" class="btn bg-black rounded-20 py-4p m-10p
                    attendance-btn-text text-white col-7 ls-15">
                    退勤
                </button>
            </form>
            <form action="/attendance/list" method="post" class="col-6 text-start">
                @csrf
                <input type="hidden" name="action" value="rest_start">
                <button type="submit" class="btn bg-white rounded-20 py-4p m-10p
                    attendance-btn-text text-black col-7 ls-15">
                    休憩入
                </button>
            </form>
        </div>
        @endif

        @if ($status === '休憩中')
        <form action="/attendance/list" method="post" class="col-6">
            @csrf
            <input type="hidden" name="action" value="rest_end">
            <button type="submit" class="btn bg-white rounded-20 mt-10p py-4p
                attendance-btn-text text-black col-7 ls-15">
                休憩戻
            </button>
        </form>
        @endif

        @if ($status === '退勤済')
        <p class="text-1vw72 mt-5p ls-15">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection
