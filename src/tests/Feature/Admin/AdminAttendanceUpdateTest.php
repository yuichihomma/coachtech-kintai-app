<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceUpdateTest extends TestCase
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

    private function adminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** ① 詳細画面の表示内容が一致 */
    public function 勤怠詳細が正しく表示される()
    {
        $admin = $this->adminUser();

        $attendance = Attendance::create([
            'user_id'   => User::factory()->create()->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'note'      => 'テスト備考',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance))
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('テスト備考');
    }

    /** ② 出勤＞退勤でエラー */
    public function test_管理者で出勤が退勤より後ならエラー()
    {
        $admin = $this->adminUser();

        $attendance = Attendance::create([
            'user_id'   => User::factory()->create()->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance),
            [
                'clock_in'  => now()->format('H:i'),
                'clock_out' => now()->subHour()->format('H:i'),
                'note'      => '修正',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ③ 休憩開始＞退勤でエラー */
    public function test_管理者で休憩開始が退勤より後ならエラー()
    {
        $admin = $this->adminUser();

        $attendance = Attendance::create([
            'user_id'   => User::factory()->create()->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now(),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance),
            [
                'clock_in'  => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->subHour()->format('H:i'),
                'note'      => '修正',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ④ 休憩終了＞退勤でエラー */
    public function test_管理者で休憩終了が退勤より後ならエラー()
    {
        $admin = $this->adminUser();

        $attendance = Attendance::create([
            'user_id'   => User::factory()->create()->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now()->subHours(2),
            'break_end'     => now(),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance),
            [
                'clock_in'  => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->subHours(3)->format('H:i'),
                'note'      => '修正',
            ]
        );

        $response->assertSessionHasErrors();
    }

    /** ⑤ 備考未入力でエラー */
    public function test_管理者で備考は必須()
    {
        $admin = $this->adminUser();

        $attendance = Attendance::create([
            'user_id'   => User::factory()->create()->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance),
            [
                'clock_in'  => now()->subHours(8)->format('H:i'),
                'clock_out' => now()->format('H:i'),
                'note'      => '',
            ]
        );

        $response->assertSessionHasErrors(['note']);
    }
}
