@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endpush


@section('content')
<div class="attendance-list-page">
  <div class="attendance-list-card">

    <h2 class="attendance-list-title">勤怠一覧</h2>

    {{-- 月切り替え --}}

    <div class="attendance-month-bar">
        <a class="attendance-month-prev"
           href="{{ route('attendance.list', ['month' => $prevMonth]) }}">
            ← 前月
        </a>

        <div class="attendance-month-current">
            {{ $currentMonth }}
        </div>

        <a class="attendance-month-next"
           href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
            翌月 →
        </a>
    </div>


    {{-- テーブル --}}
    <div class="attendance-table-wrap">
      <table class="attendance-table">
        <thead>
          <tr>
            <th class="col-date">日付</th>
            <th class="col-in">出勤</th>
            <th class="col-out">退勤</th>
            <th class="col-break">休憩</th>
            <th class="col-total">合計</th>
            <th class="col-detail">詳細</th>
          </tr>
        </thead>

        <tbody>
@for ($date = $start->copy(); $date->month === $start->month; $date->addDay())
    @php
        $attendance = $attendances[$date->toDateString()] ?? null;
    @endphp
    <tr>
        <td class="col-date">{{ $date->format('m/d(D)') }}</td>

        <td class="col-in">
            {{ optional(optional($attendance)->clock_in)->format('H:i') ?? '' }}
        </td>

        <td class="col-out">
            {{ optional(optional($attendance)->clock_out)->format('H:i') ?? '' }}
        </td>

        <td class="col-break">
            {{ $breakTimes[$date->toDateString()] ?? '00:00' }}
        </td>

        <td class="col-total">
            {{ $totals[$date->toDateString()] ?? '00:00' }}
        </td>

        <td class="col-detail">
            @if($attendance)
                <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
            @endif
        </td>
    </tr>
@endfor
</tbody>


      </table>
    </div>

  </div>
</div>
@endsection
