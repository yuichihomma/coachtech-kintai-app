<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** ① その日の全ユーザー勤怠が表示される */
    public function 全ユーザーの勤怠が表示される()
    {
        Carbon::setTestNow('2026-02-16');

        $admin = $this->adminUser();

        $users = User::factory()->count(2)->create(['role' => 'staff']);

        foreach ($users as $user) {
            Attendance::create([
                'user_id'   => $user->id,
                'work_date' => now()->toDateString(),
                'clock_in'  => now()->setTime(9, 0),
                'clock_out' => now()->setTime(18, 0),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.attendance.list'))
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** ② 現在日付が表示される */
    public function 現在の日付が表示される()
    {
        Carbon::setTestNow('2026-02-16');

        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.attendance.list'))
            ->assertSee('2026年2月16日');
    }

    /** ③ 前日ボタンで前日の勤怠が表示される */
    public function 前日の勤怠が表示される()
    {
        Carbon::setTestNow('2026-02-16');

        $admin = $this->adminUser();
        $user  = User::factory()->create(['role' => 'staff']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => now()->subDay()->toDateString(),
            'clock_in'  => now()->subDay()->setTime(9, 0),
            'clock_out' => now()->subDay()->setTime(18, 0),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.list', ['date' => now()->subDay()->toDateString()]))
            ->assertSee('09:00');
    }

    /** ④ 翌日ボタンで翌日の勤怠が表示される */
    public function 翌日の勤怠が表示される()
    {
        Carbon::setTestNow('2026-02-16');

        $admin = $this->adminUser();
        $user  = User::factory()->create(['role' => 'staff']);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => now()->addDay()->toDateString(),
            'clock_in'  => now()->addDay()->setTime(9, 0),
            'clock_out' => now()->addDay()->setTime(18, 0),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.list', ['date' => now()->addDay()->toDateString()]))
            ->assertSee('09:00');
    }
}
