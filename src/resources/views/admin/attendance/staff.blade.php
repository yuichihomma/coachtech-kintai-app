@extends('layouts.admin')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
@endpush

@section('content')
<div class="attendance-staff-page">

    <h2 class="attendance-staff-title">
        {{ $user->name }}さんの勤怠
    </h2>

    {{-- 月切り替え --}}
    <div class="month-switch">
        <a href="?month={{ $prevMonth }}" class="month-btn">◀ 前月</a>
        <span class="current-month">{{ $currentMonth }}</span>
        <a href="?month={{ $nextMonth }}" class="month-btn">翌月 ▶</a>
    </div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-card">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}</td>

                    <td>
                        {{ $attendance->clock_in
                            ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                            : '-' }}
                    </td>

                    <td>
                        {{ $attendance->clock_out
                            ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                            : '-' }}
                    </td>

                    <td>{{ $attendance->break_time ?? '' }}</td>
                    <td>{{ $attendance->total_time ?? '' }}</td>

                    <td class="detail-link">
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-message">
                        勤怠データがありません
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CSVエリア（←ここ重要） --}}
    <div class="csv-area">
        <a href="{{ route('admin.attendance.csv', [
            'user' => $user->id,
            'month' => request('month', \Carbon\Carbon::now()->format('Y-m'))
        ]) }}" class="btn btn-primary">
            CSV出力
        </a>
    </div>

</div>
@endsection
