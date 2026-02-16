@extends('layouts.admin')

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin_requests.css') }}">
@endpush

@section('content')
<div class="requests-page">

  <h2 class="requests-title">申請一覧</h2>

  {{-- タブ --}}
  <div class="requests-tabs">
    <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}"
       class="tab {{ $status === 'pending' ? 'active' : '' }}">
       承認待ち
    </a>

    <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}"
       class="tab {{ $status === 'approved' ? 'active' : '' }}">
       承認済み
    </a>
  </div>

  {{-- テーブル --}}
  <div class="requests-table-wrap">
    <table class="requests-table">
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
  @forelse ($requests as $req)
    <tr>
      <td>{{ $req->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
      <td>{{ $req->user->name }}</td>
      <td>{{ \Carbon\Carbon::parse($req->attendance->work_date)->format('Y/m/d') }}</td>
      <td>{{ $req->reason }}</td>
      <td>{{ $req->created_at->format('Y/m/d') }}</td>
      <td>
        <a class="detail-link"
           href="{{route('admin.stamp_correction_request.show', $req->id)}}">
           詳細
        </a>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" class="empty">データがありません</td>
    </tr>
  @endforelse
</tbody>

    </table>
  </div>

</div>
@endsection
