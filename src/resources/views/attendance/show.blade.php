@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endpush

@section('content')

@php
    $request = $attendance->stampCorrectionRequest;
    $isPending  = $request && $request->status === 'pending';
    $isApproved = $request && $request->status === 'approved';
@endphp

<div class="attendance-detail-page">
    <h2 class="attendance-detail-title">勤怠詳細</h2>

    <div class="attendance-detail-card">

        {{-- =========================
             ① 承認待ち
        ========================= --}}
        @if ($isPending)

            @include('attendance.partials.detail-table', [
                'attendance' => $attendance,
                'reason' => $request->reason ?? '---'
            ])

            <p class="text-danger">
                ※ 承認待ちのため修正はできません。
            </p>

        {{-- =========================
             ② 承認済み
        ========================= --}}
        @elseif ($isApproved)

            @include('attendance.partials.detail-table', [
                'attendance' => $attendance,
                'reason' => $request->reason ?? '---'
            ])

            <p class="text-danger">
                ※ 承認済みのため修正はできません。
            </p>

        {{-- =========================
             ③ 通常（修正フォーム）
        ========================= --}}
        @else

        <form method="POST" action="{{ route('attendance.apply', $attendance) }}">
            @csrf

            @if ($errors->any())
                <div class="text-danger" style="margin-bottom: 12px;">
                    {{ $errors->first() }}
                </div>
            @endif

            @php
                $break1 = $attendance->breaks->get(0);
                $break2 = $attendance->breaks->get(1);
            @endphp

            <table class="attendance-detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name="clock_in"
                            value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">

                        〜

                        <input type="time" name="clock_out"
                            value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">

                        @error('clock_in')
                            <div class="text-danger" style="margin-top: 6px;">{{ $message }}</div>
                        @enderror
                        @error('clock_out')
                            <div class="text-danger" style="margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>

                <tr>
                    <th>休憩</th>
                    <td>
                        <input type="time" name="breaks[0][start]"
                            value="{{ old('breaks.0.start', optional($break1?->break_start)->format('H:i')) }}">

                        〜

                        <input type="time" name="breaks[0][end]"
                            value="{{ old('breaks.0.end', optional($break1?->break_end)->format('H:i')) }}">

                        @error('break_start')
                            <div class="text-danger" style="margin-top: 6px;">{{ $message }}</div>
                        @enderror
                        @error('break_end')
                            <div class="text-danger" style="margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>

                <tr>
                    <th>休憩2</th>
                    <td>
                        <input type="time" name="breaks[1][start]"
                            value="{{ old('breaks.1.start', optional($break2?->break_start)->format('H:i')) }}">

                        〜

                        <input type="time" name="breaks[1][end]"
                            value="{{ old('breaks.1.end', optional($break2?->break_end)->format('H:i')) }}">
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="text-danger" style="margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <div class="attendance-detail-actions">
                <button type="submit" class="btn-edit">修正を申請</button>
            </div>

        </form>
        @endif

    </div>
</div>

@endsection
