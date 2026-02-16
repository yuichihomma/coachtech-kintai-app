@extends('layouts.admin')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_show.css') }}">
@endpush


@section('content')
<div class="attendance-detail-page">

    <h2 class="attendance-detail-title">勤怠詳細</h2>

    <div class="attendance-detail-card">
        <table class="attendance-detail-table">

            <tr>
                <th>名前</th>
                <td>{{ $request->attendance->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ optional($request->attendance->clock_in)->format('H:i') }}
                    ～
                    {{ optional($request->attendance->clock_out)->format('H:i') }}
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    @if(isset($request->attendance->breaks[0]))
                        {{ optional($request->attendance->breaks[0]->break_start)->format('H:i') }}
                        ～
                        {{ optional($request->attendance->breaks[0]->break_end)->format('H:i') }}
                    @endif
                </td>
            </tr>

            <tr>
                <th>休憩2</th>
                <td>
                    @if(isset($request->attendance->breaks[1]))
                        {{ optional($request->attendance->breaks[1]->break_start)->format('H:i') }}
                        ～
                        {{ optional($request->attendance->breaks[1]->break_end)->format('H:i') }}
                    @endif
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>{{ $request->reason }}</td>
            </tr>

        </table>

       <div class="approve-area">
    @if($request->status === 'pending')
        <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
            @csrf
            <button type="submit" class="approve-btn">承認</button>
        </form>
    @else
        <button class="approved-btn" disabled>承認済み</button>
    @endif
</div>



    </div>
</div>

@endsection
