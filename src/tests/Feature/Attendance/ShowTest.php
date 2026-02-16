<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private function staffUser(): User
    {
        return User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * ① 名前がログインユーザーの氏名になっている
     */
    #[Test]
    public function 名前がログインユーザーの氏名になっている()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee($user->name);
    }

    /**
     * ② 日付が選択した日付になっている
     */
    #[Test]
    public function 日付が選択した日付になっている()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee('2026年2月10日');
    }

    /**
     * ③ 出勤・退勤時刻が正しく表示される
     */
    #[Test]
    public function 出勤退勤時刻が正しく表示される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /**
     * ④ 休憩時間が正しく表示される
     */
    #[Test]
    public function 休憩時間が正しく表示される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => '2026-02-10 12:00:00',
            'break_end'     => '2026-02-10 13:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertSee('12:00')
            ->assertSee('13:00');
    }
}
