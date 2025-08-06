<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
        crossorigin="anonymous"
    />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body>
    <header class="bg-black">
        <div class="text-white inter d-flex justify-content-between align-items-center
                fw-normal p-1p w-100">
            <div class="col-3">
            @if (Auth::guard('admin')->check())
            <a href="/admin/attendance/list">
                <img class="w-100" src="{{ asset('logo.svg') }}">
            </a>
            @else
            <a href="/attendance">
                <img class="w-100" src="{{ asset('logo.svg') }}">
            </a>
            @endif
            </div>
            @if (!request()->is('register') && !request()->is('login') && !request()->is('email/verify') && !request()->is('admin/login'))
            <nav class="navbar col-6">
                <div class="container">
                    <ul class="navbar-nav flex-row align-items-center justify-content-end w-100 text gap-5">
                        @if (Auth::guard('admin')->check())
                        <li class="nav-item">
                            <a href="/admin/attendance/list" class="nav-link text-decoration-none text-white">勤怠一覧</a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/staff/list" class="nav-link text-decoration-none text-white">スタッフ一覧</a>
                        </li>
                        <li class="nav-item">
                            <a href="/stamp_correction_request/list" class="nav-link text-decoration-none text-white">申請一覧</a>
                        </li>
                        @elseif (request()->is('attendance') && $attendance?->clock_out)
                        <li class="nav-item">
                            <a href="/attendance/list" class="nav-link text-decoration-none text-white">今月の出勤一覧</a>
                        </li>
                        <li class="nav-item">
                            <a href="/stamp_correction_request/list" class="nav-link text-decoration-none text-white">申請一覧</a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a href="/attendance" class="nav-link text-decoration-none text-white">勤怠</a>
                        </li>
                        <li class="nav-item">
                            <a href="/attendance/list" class="nav-link text-decoration-none text-white">勤怠一覧</a>
                        </li>
                        <li class="nav-item">
                            <a href="/stamp_correction_request/list" class="nav-link text-decoration-none text-white">申請</a>
                        </li>
                        @endif
                        <li>
                            <form action="/logout" method="POST">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-decoration-none p-0 m-0 text-white">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous">
    </script>
</body>
</html>