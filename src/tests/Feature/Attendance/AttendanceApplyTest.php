<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceApplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-02-15 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function staffUser()
    {
        return User::factory()->create(['role' => 'staff']);
    }

    /** ① 出勤＞退勤でエラー */
    public function test_出勤が退勤より後ならエラー()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.apply', $attendance),
            [
                'clock_in' => now()->format('H:i'),
                'clock_out' => now()->subHour()->format('H:i'),
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ② 休憩開始＞退勤でエラー */
    public function test_休憩開始が退勤より後ならエラー()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.apply', $attendance),
            [
                'clock_in' => now()->subHours(5)->format('H:i'),
                'clock_out' => now()->subHour()->format('H:i'),
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ③ 休憩終了＞退勤でエラー */
    public function test_休憩終了が退勤より後ならエラー()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHours(2),
            'break_end' => now(),
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.apply', $attendance),
            [
                'clock_in' => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->subHours(3)->format('H:i'),
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ④ 申請理由未入力でエラー */
    public function test_申請理由は必須()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.apply', $attendance),
            [
                'clock_in' => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->format('H:i'),
                'reason' => '',
            ]
        );

        $response->assertSessionHasErrors(['reason']);
    }

    /** ⑤ 修正申請が保存される */
    public function test_打刻修正申請が作成される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($user)->post(
            route('attendance.apply', $attendance),
            [
                'clock_in' => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->format('H:i'),
                'reason' => '理由',
            ]
        );

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /** ⑥ 承認待ち一覧に表示 */
    public function test_承認待ち申請が一覧表示される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'update',
            'before_value' => '{}',
            'after_value' => '{}',
            'reason' => '理由',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'pending']))
            ->assertSee('理由');
    }

    /** ⑦ 承認済み一覧に表示 */
    public function test_承認済み申請が一覧表示される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'update',
            'before_value' => '{}',
            'after_value' => '{}',
            'reason' => '承認済',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'approved']))
            ->assertSee('承認済');
    }

    /** ⑧ 詳細ボタンで勤怠詳細へ遷移 */
    public function test_申請詳細へ遷移できる()
    {
        $user = $this->staffUser();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
    }
}
