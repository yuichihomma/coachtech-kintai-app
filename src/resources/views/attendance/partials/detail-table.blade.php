@php
    $break1 = $attendance->breaks->get(0);
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
            {{ optional($attendance->clock_in)->format('H:i') }}
            〜
            {{ optional($attendance->clock_out)->format('H:i') }}
        </td>
    </tr>

    <tr>
        <th>休憩</th>
        <td>
            {{ optional($break1?->break_start)->format('H:i') }}
            〜
            {{ optional($break1?->break_end)->format('H:i') }}
        </td>
    </tr>

    <tr>
        <th>申請理由</th>
        <td>{{ $reason }}</td>
    </tr>
</table>
