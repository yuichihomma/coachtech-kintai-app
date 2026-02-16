<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-02-14 09:00:00');
    }

    private function staffUser()
    {
        return User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 勤務外ステータスが表示される()
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 出勤中ステータスが表示される()
    {
        $user = $this->staffUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(), 
            'clock_in' => now(),
            'clock_out' => null,
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get(route('attendance.index'));

        $response->assertOk();
        $response->assertSee('出勤中');

    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 休憩中ステータスが表示される()
    {
        $user = $this->staffUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(), 
            'clock_in' => now(),
            'clock_out' => null,
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        // 休憩開始だけある状態
        $attendance->breaks()->create([
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 退勤済ステータスが表示される()
    {
        $user = $this->staffUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(), 
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
