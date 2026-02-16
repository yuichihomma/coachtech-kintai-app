<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>

    <!-- 共通CSS -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    <!-- 個別CSS -->
    @stack('css')
</head>

<body>

<header class="header">
    <div class="header-inner">

        {{-- ロゴ --}}
        <img src="{{ asset('images/logo.png') }}" class="header-logo">

        {{-- ナビ（ログイン時だけ表示） --}}
        @auth
        <nav class="header-nav">
            <a href="/attendance">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('attendance.request') }}">申請</a>

            {{-- ログアウト --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
        @endauth

    </div>
</header>


<main>
    @yield('content')
</main>

</body>
</html>
