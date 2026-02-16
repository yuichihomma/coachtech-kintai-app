<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-02-14 09:00:00');
        $this->withoutMiddleware();
    }

    private function staffUser()
    {
        return User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 出勤ボタンで勤怠が作成されステータスが出勤中になる()
    {
        $user = $this->staffUser();

        $this->actingAs($user)
            ->post('/clock-in')
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 出勤は1日1回しかできない()
    {
        $user = $this->staffUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user)->post('/clock-in');

        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->where('work_date', now()->toDateString())
                ->count()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 出勤時刻が勤怠一覧に表示される()
    {
        $user = $this->staffUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee('09:00');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 退勤済みの場合は出勤できない()
    {
        $user = $this->staffUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $this->actingAs($user)->post('/clock-in');

        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->where('work_date', now()->toDateString())
                ->count()
        );
    }
}
