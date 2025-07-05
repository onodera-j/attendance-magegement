<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance-manegement_App</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />

    @yield('css')

</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-logo">
                <a href="/admin/attendance/list"><img class="logo" src="{{ asset('img/logo.svg') }}"></a>
            </div>

            <div class="member-nav">
                <ul class="member-menu">
                    <li class="member-content"><a class="link" href="/admin/attendance/list">勤怠一覧</a></li>
                    <li class="member-content"><a class="link" href="/admin/staff/list">スタッフ一覧</a></li>
                    <li class="member-content"><a class="link" href="/stamp_correction_request/list">申請一覧</a></li>

                    @if (Auth::check())
                        <form class="form" action="/logout" method="post">
                            @csrf
                            <li class="member-content">
                                <button class="button-logout">ログアウト</button>
                            </li>
                        </form>
                    @else
                        <li class="member-content">
                            <a class="link" href="/login">ログイン</a>
                        </li>
                    @endif

                </ul>
            </div>

        </div>

    </header>
    <main>

        @yield('content')


    </main>
</body>

</html>
