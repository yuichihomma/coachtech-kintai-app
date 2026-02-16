<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請一覧
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.stamp_correction_request.list', compact('requests', 'status'));
    }

    /**
     * 申請詳細
     */
    public function show($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.stamp_correction_request.show', compact('request'));
    }

    /**
     * 承認
     */
    public function approve($id)
{
    $request = StampCorrectionRequest::with('attendance')->findOrFail($id);

    // すでに承認済みなら何もしない
    if ($request->status === 'approved') {
        return back();
    }

    $attendance = $request->attendance;

    // 修正タイプごとに反映
    switch ($request->request_type) {
        case 'update':
            $after = json_decode($request->after_value, true) ?: [];
            $workDate = Carbon::parse($attendance->work_date)->toDateString();

            if (!empty($after['clock_in'])) {
                $attendance->clock_in = Carbon::parse("{$workDate} {$after['clock_in']}");
            }

            if (!empty($after['clock_out'])) {
                $attendance->clock_out = Carbon::parse("{$workDate} {$after['clock_out']}");
            }

            if (array_key_exists('note', $after)) {
                $attendance->note = $after['note'];
            }
            break;

        case 'clock_in':
            $attendance->clock_in = $request->after_value;
            break;

        case 'clock_out':
            $attendance->clock_out = $request->after_value;
            break;

        case 'break_start':
            $attendance->breaks()->first()->update([
                'break_start' => $request->after_value
            ]);
            break;

        case 'break_end':
            $attendance->breaks()->first()->update([
                'break_end' => $request->after_value
            ]);
            break;
    }

    $attendance->save();

    // 申請ステータス更新
    $request->status = 'approved';
    $request->save();

    return redirect()
    ->route('admin.stamp_correction_request.show', $id)
    ->with('success', '承認しました');

}


    /**
     * 却下
     */
    public function reject($id)
    {
        $request = StampCorrectionRequest::findOrFail($id);

        $request->update([
            'status' => 'rejected',
        ]);

        // 勤怠ステータスを戻す
        $request->attendance->update([
            'status' => null,
        ]);

        return redirect()
            ->route('admin.stamp_correction_request.show', $id)
            ->with('success', '申請を却下しました');
    }
}
