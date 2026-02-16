@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')

<div class="admin-login-page">
    <div class="admin-login-card">
        <h2 class="admin-login-title">管理者ログイン</h2>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            {{-- 全体エラー --}}
            @if ($errors->any())
                <div class="admin-login-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">

                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">

                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="admin-login-button">
                管理者ログインする
            </button>
        </form>
    </div>
</div>

@endsection
