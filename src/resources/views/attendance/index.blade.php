@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')

<div class="attendance-page">
    <div class="attendance-card">

        {{-- 勤務状態 --}}
        <div class="attendance-status">
            {{ $status }}
        </div>


        {{-- 日付 --}}
        <div class="attendance-date">
            {{ now()->format('Y年m月d日（D）') }}
        </div>

        {{-- 時刻 --}}
<div class="attendance-time">
    @if ($attendance && $attendance->clock_out)
        {{ $attendance->clock_out->format('H:i') }}
    @else
        {{ now()->format('H:i') }}
    @endif
</div>


        {{-- ボタン制御 --}}
        @if (!$attendance)
            {{-- 未出勤 --}}
            <form method="POST" action="{{ url('/clock-in') }}">
                @csrf
                <button class="attendance-button">出勤</button>
            </form>

        @elseif (!$attendance->clock_out)
            {{-- 出勤中 --}}

            @if ($attendance->breaks->whereNull('break_end')->count())
                {{-- 休憩中 --}}
                <form method="POST" action="{{ url('/break-end') }}">
                    @csrf
                    <button class="attendance-button white">休憩戻</button>
                </form>
            @else
                {{-- 通常勤務中 --}}
                <div class="attendance-buttons">
                    <form method="POST" action="{{ url('/clock-out') }}">
                        @csrf
                        <button class="attendance-button">退勤</button>
                    </form>

                    <form method="POST" action="{{ url('/break-start') }}">
                        @csrf
                        <button class="attendance-button white">休憩入</button>
                    </form>
                </div>
            @endif

        @else
            {{-- 退勤済 --}}
            <p class="attendance-finished">お疲れ様でした。</p>
        @endif

    </div>
</div>

@endsection
