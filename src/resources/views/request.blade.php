@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh">
<div class="container pt-5p col-8">
    <h2 class="fw-bold content-title border-left pl-2p">申請一覧</h2>
    <div class="border-bottom border-black mt-5p row px-2p mx-0">
        <a href="/stamp_correction_request/list?tab=pending"
            class=" {{ $tab === 'pending' ? 'text-decoration-none text-black fw-bold col-2 text-center py-1p'
            : 'text-decoration-none text-black fw-normal col-2 text-center py-1p' }}">承認待ち</a>
        <a href="/stamp_correction_request/list?tab=approved"
            class=" {{ $tab === 'approved' ? 'text-decoration-none text-black fw-bold col-3 text-center py-1p'
            : 'text-decoration-none text-black fw-normal col-3 text-center py-1p' }}">承認済み</a>
    </div>
    <table class="table rounded-10 mt-5p table-fixed fw-bold no-border">
        <thead>
            <tr class="table-border__th">
                <th class="text-center text-73">状態</th>
                <th class="text-center text-73">名前</th>
                <th class="text-center text-73">対象日時</th>
                <th class="text-center text-73">申請理由</th>
                <th class="text-center text-73">申請日時</th>
                <th class="px-5 text-73">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $attendanceCorrectRequest)
            <tr class="table-border__td">
                @if ($tab === 'pending')
                <td class="text-center text-73">
                    承認待ち
                </td>
                @elseif ($tab === 'approved')
                <td class="text-center text-73">
                    承認済み
                </td>
                @endif
                <td class="text-center text-73">
                    {{ $attendanceCorrectRequest->attendance->user->name }}
                </td>
                <td class="text-center text-73">
                    {{ \Carbon\Carbon::parse($attendanceCorrectRequest->attendance->date)->format('Y/m/d') }}
                </td>
                <td class="text-center text-73">
                    {{ $attendanceCorrectRequest->reason }}
                </td>
                <td class="text-center text-73">
                    {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
                </td>
                <td class="px-5">
                    @if (Auth::guard('admin')->check())
                    <a href="{{ route('request.approve', $attendanceCorrectRequest) }}" class="text-decoration-none text-black">詳細</a>
                    @else
                    <a href="/attendance/{{ $attendanceCorrectRequest->attendance->id }}" class="text-decoration-none text-black">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
