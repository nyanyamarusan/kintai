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
    <header class="bg-black w-100">
        <div class="text-white inter d-flex justify-content-between align-items-center
                    fw-normal py-1p px-2p">
            <div>
            @if (Auth::check() && Auth::user()->role === 'admin')
            <a href="/admin/attendance/list" class="text-decoration-none">
                <img class="img-fluid logo" src="{{ asset('logo.svg') }}">
            </a>
            @else
            <a href="/attendance" class="text-decoration-none">
                <img class="img-fluid logo" src="{{ asset('logo.svg') }}">
            </a>
            @endif
            </div>
            @if (!request()->is('register') && !request()->is('login') && !request()->is('email/verify') && !request()->is('admin/login'))
            <nav class="navbar">
                <ul class="navbar-nav ms-auto">
                    @if (Auth::user()->role === 'admin')
                    <li class="nav-list"><a href="/admin/attendance/list" class="nav-link text-decoration-none">勤怠一覧</a></li>
                    <li class="nav-list"><a href="/admin/staff/list" class="nav-link text-decoration-none">スタッフ一覧</a></li>
                    <li class="nav-list"><a href="/stamp_correction_request/list" class="nav-link text-decoration-none">申請一覧</a></li>
                    @else
                    <li class="nav-list"><a href="/attendance" class="nav-link text-decoration-none">勤怠</a></li>
                    <li class="nav-list"><a href="/attendance/list" class="nav-link text-decoration-none">勤怠一覧</a></li>
                    <li class="nav-list"><a href="/attendance/{{ $user->id }}" class="nav-link text-decoration-none">申請</a></li>
                    @endif
                    @if (request()->is('attendance'))
                        @if ($isOffWork)
                        <li class="nav-list"><a href="/attendance/list" class="nav-link text-decoration-none">今月の出勤一覧</a></li>
                        <li class="nav-list"><a href="/stamp_correction_request/list" class="nav-link text-decoration-none">申請一覧</a></li>
                        @endif
                    @endif
                    <li>
                        <form action="/logout" method="POST">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-decoration-none p-0 m-0">ログアウト</button>
                        </form>
                    </li>
                </ul>
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
        crossorigin="anonymous"
    ></script>
</body>
</html>