<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class ListTest extends TestCase
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
     * ① 自分の勤怠情報が全て表示されている
     */
    #[Test]
    public function 自分の勤怠情報が全て表示されている()
    {
        Carbon::setTestNow('2026-02-14 12:00:00');

        $user = $this->staffUser();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /**
     * ② 現在の月が表示される
     */
    #[Test]
    public function 現在の月が表示される()
    {
        Carbon::setTestNow('2026-02-14');

        $user = $this->staffUser();

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee('2026年02月');
    }

    /**
     * ③ 前月ボタンで前月が表示される
     */
    #[Test]
    public function 前月ボタンで前月の情報が表示される()
    {
        Carbon::setTestNow('2026-02-14');

        $user = $this->staffUser();

        $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2026-01']))
            ->assertSee('2026年01月');
    }

    /**
     * ④ 翌月ボタンで翌月が表示される
     */
    #[Test]
    public function 翌月ボタンで翌月の情報が表示される()
    {
        Carbon::setTestNow('2026-02-14');

        $user = $this->staffUser();

        $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2026-03']))
            ->assertSee('2026年03月');
    }

    /**
     * ⑤ 詳細ボタンでその日の詳細画面に遷移する
     */
    #[Test]
    public function 詳細ボタンでその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow('2026-02-14');

        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-10',
            'clock_in'  => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertStatus(200);
    }
}
