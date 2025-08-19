@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh fw-bold">
    <div class="container pt-5p col-8">
        <h2 class="fw-bold content-title border-left pl-2p">勤怠詳細</h2>
        <form action="{{ route('request.approve.patch', $attendanceRequest) }}" method="post">
            @csrf
            @method('patch')
            <table class="table rounded-10 mt-5p table-fixed border-E1">
                <tr class="table-border__td">
                    <th class="px-8p py-4p text-73 col-4">名前</th>
                    <td class="py-4p px-4p">{{ $user->name }}</td>
                </tr>
                <tr class="table-border__td">
                    <th class="px-8p py-4p text-73 col-4">日付</th>
                    <td class="py-4p px-4p">
                        <div class="w-56 d-flex justify-content-between align-items-center">
                            <p class="m-0">{{ \Carbon\Carbon::parse($attendanceRequest->attendance->date)->format('Y年') }}</p>
                            <p class="m-0">{{ \Carbon\Carbon::parse($attendanceRequest->attendance->date)->translatedFormat('n月j日') }}</p>
                        </div>
                    </td>
                </tr>
                <tr class="table-border__td">
                    <th class="px-8p py-4p text-73 col-4">出勤・退勤</th>
                    <td class="py-4p px-4p">
                        <div class="w-56 d-flex justify-content-between align-items-center">
                            <p class="m-0">{{ $attendanceRequest->formatted_clock_in }}</p>
                            <span>~</span>
                            <p class="m-0">{{ $attendanceRequest->formatted_clock_out }}</p>
                        </div>
                    </td>
                </tr>
                @forelse ($attendanceRequest->requestRests as $requestRest)
                <tr class="table-border__td">
                    <th class="px-8p py-4p text-73 col-4">休憩</th>
                    <td class="py-4p px-4p">
                        <div class="w-56 d-flex justify-content-between align-items-center">
                            <p class="m-0">{{ $requestRest->formatted_start_time }}</p>
                            <span>~</span>
                            <p class="m-0">{{ $requestRest->formatted_end_time }}</p>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="table-border__td">
                    <th class="px-8p py-4p text-73 col-4">休憩</th>
                    <td class="py-4p px-4p">
                        <div class="w-56 d-flex justify-content-between align-items-center">
                            <p class="m-0"></p>
                            <span>~</span>
                            <p class="m-0"></p>
                        </div>
                    </td>
                </tr>
                @endforelse
                <tr>
                    <th class="px-8p py-4p text-73 col-4">備考</th>
                    <td class="py-4p px-4p">{{ $attendanceRequest->reason }}</td>
                </tr>
            </table>
            <div class="text-end">
                @if ($attendanceRequest->is_pending_request)
                <button type="submit" class="btn bg-black rounded-2 mt-2p text-1vw45 text-white w-12 ls-15 fw-bold">承認</button>
                @else
                <button disabled class="btn bg-gray-69 rounded-2 mt-2p text-1vw45 text-white w-12 ls-15 fw-bold">承認済み</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
