@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endpush

@section('content')
<div class="verify-wrapper">

    <div class="verify-card">

        <p class="verify-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- 認証ページへ --}}
        <a href="mailto:" class="verify-btn">
            認証はこちらから
        </a>

        {{-- 再送 --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-resend">
                認証メールを再送する
            </button>
        </form>

    </div>
</div>
@endsection
