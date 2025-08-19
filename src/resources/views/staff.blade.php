@extends('layouts.app')

@section('content')
<div class="bg-f0eff2 inter m-h-100vh">
<div class="container pt-5p col-8">
    <h2 class="fw-bold content-title border-left pl-2p">スタッフ一覧</h2>
    <table class="table rounded-10 mt-5p table-fixed fw-bold no-border">
        <thead>
            <tr class="table-border__th">
                <th class="text-center text-73">名前</th>
                <th class="text-center text-73">メールアドレス</th>
                <th class="text-center text-73">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr class="table-border__td">
                <td class="text-center text-73">
                    {{ $user->name }}
                </td>
                <td class="text-center text-73">
                    {{ $user->email }}
                </td>
                <td class="text-center">
                    <a href="/admin/attendance/staff/{{ $user->id }}" class="text-decoration-none text-black">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
