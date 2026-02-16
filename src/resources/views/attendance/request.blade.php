@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endpush

@section('content')
<div class="request-page">

    <h2 class="request-title">申請一覧</h2>

    <div class="request-tabs">
    <a href="{{ route('attendance.request', ['status' => 'pending']) }}"
       class="{{ $status === 'pending' ? 'active' : '' }}">
       承認待ち
    </a>

    <a href="{{ route('attendance.request', ['status' => 'approved']) }}"
       class="{{ $status === 'approved' ? 'active' : '' }}">
       承認済み
    </a>
</div>


    <div class="request-card">
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($requests as $request)
<tr>
    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
    <td>{{ $request->attendance->user->name }}</td>
    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
    <td>{{ $request->reason }}</td>
    <td>{{ $request->created_at->format('Y/m/d') }}</td>
    <td>
        <a href="{{ route('attendance.show', $request->attendance_id) }}">詳細</a>
    </td>
</tr>
@empty
<tr>
    <td colspan="6">申請はありません</td>
</tr>
@endforelse



            </tbody>
        </table>
    </div>

</div>
@endsection
