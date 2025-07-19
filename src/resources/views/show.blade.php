@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh fw-bold">
    <div class="container pt-5p col-8">
        <h2 class="fw-bold content-title border-left pl-2p">勤怠詳細</h2>
        @if (Auth::check() && !Auth::guard('admin')->check() && $attendance->is_pending_request)
        <table class="table rounded-10 mt-5p table-fixed">
            <tr>
                <th class="px-8p py-4p text-73">名前</th>
                <td class="py-4p px-3p">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th class="px-8p py-4p text-73">日付</th>
                <td class="py-4p px-3p">{{ $attendance->date }}</td>
            </tr>
            <tr>
                <th class="px-8p py-4p text-73">出勤・退勤</th>
                <td class="py-4p px-3pp">
                    <div class="w-60 d-flex justify-content-between align-items-center">
                        <p>{{ $attendance->clock_in }}</p>
                        <span>~</span>
                        <p>{{ $attendance->clock_out }}</p>
                    </div>
            </tr>
            @forelse ($attendance->restTimes as $restTime)
            <tr>
                <th class="px-8p py-4p text-73">休憩</th>
                <td class="py-4p px-3p">
                    <div class="w-60 d-flex justify-content-between align-items-center">
                        <p>{{ $restTime->start_time }}</p>
                        <span>~</span>
                        <p>{{ $restTime->end_time }}</p>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <th class="px-8p py-4p text-73">休憩</th>
                <td class="py-4p px-3p">
                    <div class="w-60 d-flex justify-content-between align-items-center">
                        <p></p>
                        <span>~</span>
                        <p></p>
                    </div>
                </td>
            </tr>
            @endforelse
            <tr>
                <th class="px-8p py-4p text-73">備考</th>
                <td class="py-4p px-3p">{{ $attendance->request->reason }}</td>
            </tr>
        </table>
        <p class="text-1vw19 mt-2p text-end fw-extrabold text-FF0000">*承認待ちのため修正はできません。</p>
        @else
        @php
        $formAction = Auth::guard('admin')->check() ? '/attendance/list' : '/stamp_correction_request/list';
        @endphp
        <form action="{{ $formAction }}" method="post" class="mt-5p">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <table class="table rounded-10 mt-5p table-fixed">
                <tr>
                    <th class="px-8p py-4p text-73 col-4">名前</th>
                    <td class="py-4p px-3p">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th class="px-8p py-4p text-73">日付</th>
                    <td class="py-4p px-3p">{{ $attendance->date }}</td>
                </tr>
                <tr>
                    <th id="clock_label" class="px-8p py-4p text-73">出勤・退勤</th>
                    <td class="py-4p">
                        <div class="w-60 d-flex justify-content-between align-items-center">
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="clock_in" name="clock_in"
                                aria-labelledby="clock_label" value="{{ $attendance->clock_in ?? '' }}">
                            <span>~</span>
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="clock_out" name="clock_out"
                                aria-labelledby="clock_label" value="{{ $attendance->clock_out ?? '' }}">
                        </div>
                        @error('clock_in')
                            <p class="text-danger">{{ $message }}</p>
                        @else
                            @error('clock_out')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        @enderror
                    </td>
                </tr>
                @foreach ($attendance->restTimes as $index => $restTime)
                <tr>
                    <th id="rest_label_{{ $index }}" class="px-8p py-4p text-73">休憩{{ $index + 1 }}</th>
                    <td class="py-4p">
                        <div class="w-60 d-flex justify-content-between align-items-center">
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="rest_start_{{ $index }}" name="rest[{{ $index }}][start_time]"
                                aria-labelledby="rest_label_{{ $index }}" value="{{ $restTime->start_time ?? '' }}">
                            <span>~</span>
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="rest_end_{{ $index }}" name="rest[{{ $index }}][end_time]"
                                aria-labelledby="rest_label_{{ $index }}" value="{{ $restTime->end_time ?? '' }}">
                        </div>
                        @if ($errors->has("rest.$index.start_time"))
                            <p class="text-danger">{{ $errors->first("rest.$index.start_time") }}</p>
                        @endif
                        @if ($errors->has("rest.$index.end_time"))
                            <p class="text-danger">{{ $errors->first("rest.$index.end_time") }}</p>
                        @endif
                    </td>
                </tr>
                @endforeach
                @php
                $nextIndex = count($attendance->restTimes);
                @endphp
                <tr>
                    <th id="rest_label_{{ $nextIndex }}" class="px-8p py-4p text-73">休憩{{ $nextIndex + 1 }}</th>
                    <td class="py-4p">
                        <div class="w-60 d-flex justify-content-between align-items-center">
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="rest_start_{{ $nextIndex }}" name="rest[{{ $nextIndex }}][start_time]"
                                aria-labelledby="rest_label_{{ $nextIndex }}" value="">
                            <span>~</span>
                            <input type="text" class="form-control rounded-1 w-30 fw-bold text-center py-0" id="rest_end_{{ $nextIndex }}" name="rest[{{ $nextIndex }}][end_time]"
                                aria-labelledby="rest_label_{{ $nextIndex }}" value="">
                        </div>
                        @if ($errors->has("rest.$nextIndex.start_time"))
                            <p class="text-danger">{{ $errors->first("rest.$nextIndex.start_time") }}</p>
                        @endif
                        @if ($errors->has("rest.$nextIndex.end_time"))
                            <p class="text-danger">{{ $errors->first("rest.$nextIndex.end_time") }}</p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="px-8p py-4p text-73">備考</th>
                    <td>
                    <textarea class="form-control rounded-1 w-60 fw-bold resize-none" id="reason" name="reason"></textarea>
                    @error('reason')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                    </td>
                </tr>
            </table>
            <div class="text-end">
                <button type="submit" class="btn bg-black rounded-2 mt-2p text-1vw45 text-white w-12 ls-15 fw-bold">修正</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection