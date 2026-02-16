@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
<div class="register-page">

    <div class="register-card">

        <h1 class="register-title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- 名前 --}}
            <div class="form-group">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

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

            {{-- 確認 --}}
            <div class="form-group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirmation">
            </div>

            <button class="register-button">登録する</button>

            <p class="login-link">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </p>

        </form>

    </div>

</div>
@endsection
