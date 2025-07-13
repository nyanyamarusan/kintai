@extends('layouts.app')

@section('content')
<div class="bg-info d-flex justify-content-center align-items-center">
    <div class="inter fw-bold text-center">
        <p class="bg-gray-c8 rounded-pill text-gray-69 status">勤務外</p>

        @php
        \Carbon\Carbon::setLocale('ja');
        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->isoFormat('ddd');
        @endphp

        <p>{{ $now->year }}年{{ $now->month }}月{{ $now->day }}日({{ $dayOfWeek }})</p>
        <p>{{ $now->format('H:i') }}</p>
        <form action="/attendance/list" method="post">
            @csrf
            <button type="submit" class="btn btn-text bg-black rounded-2 w-100 mt-10p title">出勤</button>
        </form>
    </div>
</div>
@endsection
