@extends('layouts.admin')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_show.css') }}">
@endpush

@section('content')
@php
    $break1 = $attendance->breaks->get(0);
    $break2 = $attendance->breaks->get(1);
    $isPending = $attendance->stampCorrectionRequest
        && $attendance->stampCorrectionRequest->status === 'pending';
@endphp

<div class="attendance-detail-page">
    <h2 class="attendance-detail-title">勤怠詳細</h2>

    <div class="attendance-detail-card">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        @if ($isPending)
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
                        {{ optional($attendance->clock_in)->format('H:i') }}
                        <span class="time-sep">〜</span>
                        {{ optional($attendance->clock_out)->format('H:i') }}
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        {{ optional($break1?->break_start)->format('H:i') }}
                        <span class="time-sep">〜</span>
                        {{ optional($break1?->break_end)->format('H:i') }}
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        {{ optional($break2?->break_start)->format('H:i') }}
                        <span class="time-sep">〜</span>
                        {{ optional($break2?->break_end)->format('H:i') }}
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>{{ $attendance->note ?? '---' }}</td>
                </tr>
            </table>

            <p class="alert-error" style="margin-top: 16px;">
                ※ 承認待ちのため修正はできません。
            </p>

            <div class="detail-actions">
                <a href="{{ route('admin.attendance.list', ['date' => $attendance->work_date]) }}" class="btn">戻る</a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
                @csrf
                @method('PUT')

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
                            <input class="time-input" type="time" name="clock_in"
                                value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                            <span class="time-sep">〜</span>
                            <input class="time-input" type="time" name="clock_out"
                                value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                            @error('clock_in')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('clock_out')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            <input class="time-input" type="time" name="break1_start"
                                value="{{ old('break1_start', optional($break1?->break_start)->format('H:i')) }}">
                            <span class="time-sep">〜</span>
                            <input class="time-input" type="time" name="break1_end"
                                value="{{ old('break1_end', optional($break1?->break_end)->format('H:i')) }}">
                            @error('break1_start')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('break1_end')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <input class="time-input" type="time" name="break2_start"
                                value="{{ old('break2_start', optional($break2?->break_start)->format('H:i')) }}">
                            <span class="time-sep">〜</span>
                            <input class="time-input" type="time" name="break2_end"
                                value="{{ old('break2_end', optional($break2?->break_end)->format('H:i')) }}">
                            @error('break2_start')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('break2_end')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('break_time')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="note-textarea" name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                            @error('note')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="detail-actions">
                    <a href="{{ route('admin.attendance.list', ['date' => $attendance->work_date]) }}" class="btn">戻る</a>
                    <button type="submit" class="btn btn-primary">修正</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
