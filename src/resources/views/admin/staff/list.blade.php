@extends('layouts.admin')

@section('content')
<div class="admin-staff-page">
    <h2 class="admin-staff-title">スタッフ一覧</h2>

    <div class="admin-staff-card">
        <table class="admin-staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffs as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td class="detail-link">
                            <a href="{{ route('admin.attendance.staff', $staff->id) }}">詳細</a>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-message">
                            スタッフが登録されていません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
