<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        Carbon::setTestNow('2026-02-14 10:00:00');
    }

    private function staffUser(): User
    {
        return User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    #[Test]
    public function 休憩開始ボタンで休憩中ステータスになる(): void
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)
    ->post('/break-start')
    ->assertRedirect();

$this->assertDatabaseHas('breaks', [
    'attendance_id' => $attendance->id,
    'break_end' => null,
]);

$this->actingAs($user)
    ->get('/attendance')
    ->assertSee('休憩中');

    }

    #[Test]
    public function 休憩は1日に何回でもできる(): void
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/break-start');
        $this->actingAs($user)->post('/break-end');

        $this->actingAs($user)->post('/break-start');
        $this->actingAs($user)->post('/break-end');

        $this->assertEquals(2, $attendance->breaks()->count());
    }

    #[Test]
    public function 休憩終了ボタンで出勤中に戻る(): void
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/break-start');
        $this->actingAs($user)->post('/break-end');

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    #[Test]
    public function 休憩時間が勤怠一覧に表示される(): void
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
            'clock_out' => now(),
        ]);

        $attendance->breaks()->create([
            'break_start' => now()->subHour(),
            'break_end' => now()->subMinutes(30),
        ]);

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee('0:30');
    }
}
