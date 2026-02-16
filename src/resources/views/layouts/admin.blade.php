<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/admin_attendance_show.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_common.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_staff.css') }}">
  @stack('css')

  <title>COACHTECH</title>
</head>
<body>

<header class="admin-header">
  <div class="admin-header-inner">
    <div class="admin-logo">
      <a href="{{ route('admin.attendance.list') }}">
        <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
      </a>
    </div>

    <nav class="admin-nav">
      <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
      <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
      <a href="{{ route('admin.stamp_correction_request.list') }}">申請一覧</a>

      <form method="POST" action="{{ route('logout') }}" class="admin-logout-form">
        @csrf
        <button type="submit">ログアウト</button>
      </form>
    </nav>
  </div>
</header>

<main class="admin-main">
  @yield('content')
</main>

</body>
</html>
