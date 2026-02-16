<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CurrentTimeTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 現在日時が画面に表示される()
    {
        // 時刻固定
        Carbon::setTestNow(Carbon::create(2026, 2, 13, 9, 0, 0));

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'staff',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);

        // 09:00 が表示されているか
        $response->assertSee('09:00');
    }
}
