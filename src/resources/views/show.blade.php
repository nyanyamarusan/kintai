@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh fw-bold">
    <div class="container pt-5p col-8">
        <h2 class="fw-bold content-title border-left pl-2p">勤怠詳細</h2>
        @if (Auth::check() && !Auth::guard('admin')->check() && $attendance->is_pending_request)
        <table class="table rounded-10 mt-5p table-fixed">
            <tr>
                <th class="text-73">名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th class="text-73">日付</th>
                <td>{{ $attendance->date }}</td>
            </tr>
            <tr>
                <th class="text-73">出勤・退勤</th>
                <td class="d-flex align-items-center justify-content-center">
                    <p class="form-control border-0">{{ $attendance->clock_in }}</p>
                    <span>~</span>
                    <p class="form-control border-0">{{ $attendance->clock_out }}</p>
                </td>
            </tr>
            @forelse ($attendance->restTimes as $restTime)
            <tr>
                <th class="text-73">休憩</th>
                <td>
                    <p class="form-control border-0">{{ $restTime->start_time }}</p>
                    <span>~</span>
                    <p class="form-control border-0">{{ $restTime->end_time }}</p>
                </td>
            </tr>
            @empty
            <tr>
                <th class="text-73">休憩</th>
                <td>
                    <p class="form-control border-0"></p>
                    <span>~</span>
                    <p class="form-control border-0"></p>
                </td>
            </tr>
            @endforelse
            <tr>
                <th class="text-73">備考</th>
                <td class="form-control border-0">{{ $attendance->request->reason }}</td>
            </tr>
        </table>
        <p>*承認待ちのため修正はできません。</p>
        @else
        @php
        $formAction = Auth::guard('admin')->check() ? '/attendance/list' : '/stamp_correction_request/list';
        @endphp
        <form action="{{ $formAction }}" method="post" class="mt-5p">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <table class="table rounded-10 mt-5p table-fixed">
                <tr>
                    <th class="text-73">名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th class="text-73">日付</th>
                    <td>{{ $attendance->date }}</td>
                </tr>
                <tr>
                    <th id="clock_label" class="text-73">出勤・退勤</th>
                    <td>
                        <input type="text" class="form-control rounded-1" id="clock_in" name="clock_in"
                            aria-labelledby="clock_label" value="{{ $attendance->clock_in }}">
                        <span>~</span>
                        <input type="text" class="form-control rounded-1" id="clock_out" name="clock_out"
                            aria-labelledby="clock_label" value="{{ $attendance->clock_out }}">
                    </td>
                </tr>
                @foreach ($attendance->restTimes as $index => $restTime)
                <tr>
                    <th id="rest_label_{{ $index }}" class="text-73">休憩{{ $index + 1 }}</th>
                    <td>
                    <input type="text" class="form-control rounded-1" id="rest_start_{{ $index }}" name="rest[{{ $index }}][start_time]"
                        aria-labelledby="rest_label_{{ $index }}" value="{{ $restTime->start_time }}">
                    <span>~</span>
                    <input type="text" class="form-control rounded-1" id="rest_end_{{ $index }}" name="rest[{{ $index }}][end_time]"
                        aria-labelledby="rest_label_{{ $index }}" value="{{ $restTime->end_time }}">
                    </td>
                </tr>
                @endforeach
                @php
                $nextIndex = count($attendance->restTimes);
                @endphp
                <tr>
                    <th id="rest_label_{{ $nextIndex }}" class="text-73">休憩{{ $nextIndex + 1 }}</th>
                    <td>
                    <input type="text" class="form-control rounded-1" id="rest_start_{{ $nextIndex }}" name="rest[{{ $nextIndex }}][start_time]"
                        aria-labelledby="rest_label_{{ $nextIndex }}" value="">
                    <span>~</span>
                    <input type="text" class="form-control rounded-1" id="rest_end_{{ $nextIndex }}" name="rest[{{ $nextIndex }}][end_time]"
                        aria-labelledby="rest_label_{{ $nextIndex }}" value="">
                    </td>
                </tr>
                <tr>
                    <th class="text-73">備考</th>
                    <td>
                    <textarea class="form-control rounded-1" id="reason" name="reason"></textarea>
                    </td>
                </tr>
            </table>
            <button type="submit" class="btn bg-black rounded-20 mt-10p py-4p
                attendance-btn-text text-white col-7 ls-15">修正</button>
        </form>
        @endif
    </div>
</div>
@endsection