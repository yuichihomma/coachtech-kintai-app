<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * スタッフ別勤怠一覧（月表示）
     */
    public function staff(Request $request, User $user)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->work_date)->toDateString());

        return view('admin.attendance.staff', [
            'user'         => $user,
            'attendances'  => $attendances,
            'currentMonth' => $start->format('Y/m'),
            'prevMonth'    => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth'    => $start->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /**
     * 日別勤怠一覧
     */
    public function list(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();

        // 全スタッフ
        $users = User::where('role', 'staff')->get();

        // 当日の勤怠（user_idキー）
        $attendanceMap = Attendance::with('breaks')
            ->whereDate('work_date', $date)
            ->get()
            ->keyBy('user_id');

        // 表示用整形
        $rows = $users->map(function ($user) use ($attendanceMap) {

            $attendance = $attendanceMap->get($user->id);

            return [
                'id'        => $attendance?->id,
                'name'      => $user->name,
                'clock_in'  => $attendance?->clock_in?->format('H:i'),
                'clock_out' => $attendance?->clock_out?->format('H:i'),
                'break'     => $attendance?->break_time ?? '0:00',
                'total'     => $attendance?->total_time ?? '0:00',
            ];
        });

        return view('admin.attendance.list', [
            'rows'        => $rows,
            'currentDate' => $date,
            'prevDate'    => $prevDate,
            'nextDate'    => $nextDate,
        ]);
    }

    /**
     * 勤怠詳細
     */
    public function show(Attendance $attendance)
    {
        $attendance->load('breaks', 'user');

        return view('admin.attendance.show', compact('attendance'));
    }

   public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance)
{
    // 申請中は更新禁止
    if ($attendance->stampCorrectionRequest
        && $attendance->stampCorrectionRequest->status === 'pending') {

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('error', '承認待ちのため修正できません。');
    }

    $validated = $request->validated();

    $data = array_merge([
        'break1_start' => null,
        'break1_end'   => null,
        'break2_start' => null,
        'break2_end'   => null,
    ], $validated);

    // =========================
    // 勤務時間外の休憩チェック
    // =========================
    $clockIn  = Carbon::parse($data['clock_in']);
    $clockOut = Carbon::parse($data['clock_out']);
    $existingBreaks = $attendance->breaks()->orderBy('id')->get();
    $breakPairs = [
        [
            'start' => $request->exists('break1_start')
                ? $data['break1_start']
                : optional($existingBreaks->get(0)?->break_start)->format('H:i'),
            'end'   => $request->exists('break1_end')
                ? $data['break1_end']
                : optional($existingBreaks->get(0)?->break_end)->format('H:i'),
        ],
        [
            'start' => $request->exists('break2_start')
                ? $data['break2_start']
                : optional($existingBreaks->get(1)?->break_start)->format('H:i'),
            'end'   => $request->exists('break2_end')
                ? $data['break2_end']
                : optional($existingBreaks->get(1)?->break_end)->format('H:i'),
        ],
    ];

    foreach ($breakPairs as $break) {
        if ($break['start']) {
            $start = Carbon::parse($break['start']);
            if ($start < $clockIn || $start > $clockOut) {
                return back()
                    ->withErrors(['break_time' => '休憩時間が不適切な値です'])
                    ->withInput();
            }
        }

        if ($break['end']) {
            $end = Carbon::parse($break['end']);
            if ($end < $clockIn || $end > $clockOut) {
                return back()
                    ->withErrors(['break_time' => '休憩時間が不適切な値です'])
                    ->withInput();
            }
        }
    }

    // =========================
    // 更新処理
    // =========================
    $attendance->update([
        'clock_in'  => $clockIn,
        'clock_out' => $clockOut,
        'note'      => $data['note'],
    ]);

    $breaks = $existingBreaks;

    // 休憩1
    if ($request->exists('break1_start') || $request->exists('break1_end')) {

        $break1 = $breaks->get(0)
            ?? $attendance->breaks()->create([]);

        $break1->update([
            'break_start' => $data['break1_start'] ? Carbon::parse($data['break1_start']) : null,
            'break_end'   => $data['break1_end'] ? Carbon::parse($data['break1_end']) : null,
        ]);
    }

    // 休憩2
    if ($request->exists('break2_start') || $request->exists('break2_end')) {

        $break2 = $breaks->get(1)
            ?? $attendance->breaks()->create([]);

        $break2->update([
            'break_start' => $data['break2_start'] ? Carbon::parse($data['break2_start']) : null,
            'break_end'   => $data['break2_end'] ? Carbon::parse($data['break2_end']) : null,
        ]);
    }

    return redirect()
        ->route('admin.attendance.show', $attendance->id)
        ->with('success', '勤怠を更新しました');
}




      // 承認待ち一覧
public function requests()
{
    $requests = StampCorrectionRequest::with('attendance.user')
        ->where('status', 'pending')
        ->latest()
        ->get();

    return view('admin.requests', compact('requests'));
}

// 承認済み一覧
public function approve(StampCorrectionRequest $request)
{
    $attendance = $request->attendance;

    // after_value を反映
    $after = json_decode($request->after_value, true);

    $attendance->update([
        'clock_in'  => $after['clock_in'],
        'clock_out' => $after['clock_out'],
        'note'      => $after['note'],
        'status'    => 'approved',   // ★ここ重要
    ]);

    // 申請側も承認済みに
    $request->update([
        'status' => 'approved',
    ]);

    return back()->with('success', '承認しました');
}


public function csv(Request $request, User $user)
{
    // ★ 月取得
    $month = $request->query('month', now()->format('Y-m'));

    // ★ 月の開始・終了
    $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

    // ★ ファイル名
    $fileName = "attendance_{$user->id}_{$month}.csv";

    // ★ 月だけ取得
    $attendances = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereBetween('work_date', [$start, $end])
        ->orderBy('work_date')
        ->get();

    $headers = [
        "Content-Type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename={$fileName}",
    ];

    return response()->stream(function () use ($attendances) {

        $handle = fopen('php://output', 'w');

        // Excel文字化け防止
        fwrite($handle, "\xEF\xBB\xBF");

        // ヘッダー
        fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

        foreach ($attendances as $attendance) {

            $breakMinutes = $attendance->breaks->sum(function ($b) {
                return optional($b->break_end)
                    ? $b->break_start->diffInMinutes($b->break_end)
                    : 0;
            });

            $workMinutes = $attendance->clock_in && $attendance->clock_out
                ? $attendance->clock_in->diffInMinutes($attendance->clock_out) - $breakMinutes
                : 0;

            fputcsv($handle, [
                $attendance->work_date,
                optional($attendance->clock_in)->format('H:i'),
                optional($attendance->clock_out)->format('H:i'),
                gmdate('H:i', $breakMinutes * 60),
                gmdate('H:i', $workMinutes * 60),
            ]);
        }

        fclose($handle);

    }, 200, $headers);
}




}
