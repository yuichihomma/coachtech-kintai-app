@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="login-page">

    <div class="login-card">

        <h1 class="login-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- メール --}}
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- パスワード --}}
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button class="login-button">ログインする</button>

            <p class="register-link">
                <a href="{{ route('register') }}">会員登録はこちら</a>
            </p>

        </form>

    </div>

</div>
@endsection
