<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminUserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-02-16 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff()
    {
        return User::factory()->create(['role' => 'staff']);
    }

    /** ① 全ユーザーの氏名・メールが表示 */
    public function test_管理者は全ユーザーを表示できる()
    {
        $admin = $this->admin();

        $users = User::factory()->count(3)->create(['role' => 'staff']);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        foreach ($users as $user) {
            $response->assertSee($user->name)
                     ->assertSee($user->email);
        }
    }

    /** ② 特定ユーザーの勤怠一覧表示 */
    public function test_管理者はユーザーの勤怠一覧を表示できる()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.staff', $staff))
            ->assertSee($attendance->work_date->format('m/d'));
    }

    /** ③ 前月表示 */
    public function test_前月の勤怠が表示される()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => now()->subMonth()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $staff->id,
                'month' => now()->subMonth()->format('Y-m'),
            ]))
            ->assertSee($attendance->work_date->format('m/d'));
    }

    /** ④ 翌月表示 */
    public function test_翌月の勤怠が表示される()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $staff->id,
                'month' => now()->addMonth()->format('Y-m'),
            ]))
            ->assertSee($attendance->work_date->format('m/d'));
    }

    /** ⑤ 詳細遷移 */
    public function test_管理者は勤怠詳細を開ける()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance))
            ->assertStatus(200);
    }
}
