@extends('layouts.admin')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendances.css') }}">
@endpush

@section('content')
<div class="admin-attendance-page">
  <div class="admin-attendance-card">

    <h2 class="admin-attendance-title">
      {{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠
    </h2>

    {{-- 日付ナビ --}}
    <div class="admin-attendance-datebar">
      <a class="datebar-btn"
         href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
        ← 前日
      </a>

      <div class="datebar-center">
        <span class="datebar-icon">📅</span>
        <span class="datebar-date">
          {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
        </span>
      </div>

      <a class="datebar-btn datebar-btn-right"
         href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">
        翌日 →
      </a>
    </div>

    {{-- テーブル --}}
    <div class="admin-attendance-tablewrap">
      <table class="admin-attendance-table">
        <thead>
          <tr>
            <th class="col-name">名前</th>
            <th class="col-time">出勤</th>
            <th class="col-time">退勤</th>
            <th class="col-break">休憩</th>
            <th class="col-sum">合計</th>
            <th class="col-detail">詳細</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($rows as $row)
            <tr>
              {{-- 名前 --}}
              <td class="td-name">{{ $row['name'] }}</td>

              {{-- 出勤 --}}
              <td class="td-time">{{ $row['clock_in'] ?? '' }}</td>

              {{-- 退勤 --}}
              <td class="td-time">{{ $row['clock_out'] ?? '' }}</td>

              {{-- 休憩 --}}
              <td class="td-break">{{ $row['break'] ?? '0:00' }}</td>

              {{-- 合計 --}}
              <td class="td-sum">{{ $row['total'] ?? '0:00' }}</td>

              {{-- 詳細 --}}
              <td class="td-detail">
                @if (!empty($row['id']))
                  <a class="detail-link"
                     href="{{ route('admin.attendance.show', $row['id']) }}">
                    詳細
                  </a>
                @else
                  -
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td class="td-empty" colspan="6">
                該当する勤怠データがありません。
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
