<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test; // ← ★これ必須


class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 退勤ボタンで退勤済みステータスになる()
    {
        Carbon::setTestNow('2026-02-14 18:00:00');

        $user = User::factory()->create([
    'role' => 'staff',
    'email_verified_at' => now(),
]);


        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => now()->subHours(8),
        ]);

        $this->actingAs($user)
            ->post('/clock-out')
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_out' => now(),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('退勤済');
    }

    #[Test]
public function 退勤時刻が勤怠一覧に表示される()
{
    Carbon::setTestNow(Carbon::create(2026, 2, 14, 18, 0, 0));

    $user = User::factory()->create([
        'role' => 'staff',
        'email_verified_at' => now(),
    ]);

    $attendance = Attendance::create([
        'user_id'   => $user->id,
        'work_date' => now()->toDateString(),
        'clock_in'  => now()->subHours(8),
        'clock_out' => now()->copy()->startOfMinute(), // ← ★ここ重要
    ]);

    $this->actingAs($user)
        ->get('/attendance')
        ->assertSee($attendance->clock_out->format('H:i')); // ← 文字列固定禁止
}

}
