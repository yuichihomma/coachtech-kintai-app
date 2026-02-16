<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceApplyRequest;




class AttendanceController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $today = now()->toDateString();
    
    $attendance = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereDate('work_date', now())
        ->first();

    // ★ 状態判定
    if (!$attendance) {
        $status = '勤務外';
    } elseif ($attendance->clock_out) {
        $status = '退勤済';
    } elseif ($attendance->breaks->whereNull('break_end')->count() > 0) {
        $status = '休憩中';
    } else {
        $status = '出勤中';
    }

    return view('attendance.index', compact('attendance', 'status'));
}



    public function clockIn()
    {
        $user = Auth::user();
        $today =now()->toDateString();

        //既に出勤している場合は弾く
        if (Attendance::where('user_id', $user->id)->where('work_date', $today)->exists()){
            return back()->with('error', '既に出勤済みです。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now(),
        ]);

        return redirect()->route('attendance.index');

    }

    public function clockOut()
{
    $user = Auth::user();
    $today = now()->toDateString();

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->firstOrFail();

    if ($attendance->breaks()->whereNull('break_end')->exists()) {
        return redirect()->route('attendance.index')
            ->with('error', '休憩終了後に退勤してください。');
    }

    $attendance->update([
        'clock_out' => now(),
    ]);

    return redirect()->route('attendance.index')
        ->with('success', '退勤しました。');
}


    public function breakStart()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->firstOrFail();

        //既に休憩中なら弾く
        if ($attendance->breaks()->whereNull('break_end')->exists()){
            return back()->with('error', '既に休憩中です。');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        return back()->with('success', '休憩を開始しました。');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->firstOrFail();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->first();

        if (!$break){
            return back()->with('error', '休憩中ではありません。');
        }

        $break->update([
            'break_end' => now(),
        ]);

        return back()->with('success', '休憩を終了しました。');
    }

    public function list(Request $request)
{

    $user = auth()->user();

    // 表示する月
    $month = $request->input('month', now()->format('Y-m'));

    $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $end   = $start->copy()->endOfMonth();
    $currentMonth = $start->format('Y年m月');

    // 勤怠取得（休憩も）
    $attendances = Attendance::with('breaks')
    ->where('user_id', $user->id)
    ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
    ->get()
    ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

$breakTimes = [];
$totals = [];

foreach ($attendances as $date => $row) {



    if (!$row->clock_in || !$row->clock_out) {
        $breakTimes[$date] = '00:00';
        $totals[$date] = '00:00';
        continue;
    }

    $workMinutes = $row->clock_in->diffInMinutes($row->clock_out);



    // ▼ 休憩時間（JSTに変換）
    $breakMinutes = $row->breaks->sum(function ($break) {
    if (!$break->break_start || !$break->break_end) return 0;

   return $break->break_start->diffInMinutes($break->break_end);

});


    // ▼ 合計
    $totalMinutes = max(0, $workMinutes - $breakMinutes);

    // ▼ 表示用
    $breakTimes[$date] =
        floor($breakMinutes / 60) . ':' .
        str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT);

    $totals[$date] =
        floor($totalMinutes / 60) . ':' .
        str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT);
}


    $prevMonth = $start->copy()->subMonth()->format('Y-m');
    $nextMonth = $start->copy()->addMonth()->format('Y-m');

    return view('attendance.list', compact(
        'start',
        'end',
        'attendances',
        'prevMonth',
        'nextMonth',
        'month',
        'currentMonth',
        'breakTimes',
        'totals'
    ));
}


    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        return view('attendance.show', compact('attendance'));
    }

public function apply(AttendanceApplyRequest $request, Attendance $attendance)
{
    if ($attendance->user_id !== auth()->id()) {
        abort(403);
    }

    // 修正前
    $before = [
        'clock_in'  => $attendance->clock_in,
        'clock_out' => $attendance->clock_out,
        'note'      => $attendance->note,
    ];

    // 修正後
    $after = [
        'clock_in'  => $request->clock_in,
        'clock_out' => $request->clock_out,
        'note'      => $request->reason,
    ];

    // ★ 正しいテーブルに保存
    StampCorrectionRequest::create([
        'user_id'       => auth()->id(),
        'attendance_id' => $attendance->id,
        'request_type'  => 'update',
        'before_value'  => json_encode($before),
        'after_value'   => json_encode($after),
        'reason'        => $request->reason,
        'status'        => 'pending',
    ]);

    $attendance->update([
        'status' => 'pending',
    ]);

    return redirect()
        ->route('attendance.show', $attendance->id)
        ->with('success', '修正申請を送信しました');

}


public function requestList(Request $request)
{
    $user = auth()->user();
    $status = $request->query('status', 'pending');

    $requests = StampCorrectionRequest::with('attendance.user')
        ->where('user_id', $user->id)
        ->where('status', $status)
        ->latest()
        ->get();

    return view('attendance.request', compact('requests', 'status'));
}





}
