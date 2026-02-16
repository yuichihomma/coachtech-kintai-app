<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** ① 登録時に認証メール送信 */
    public function test_会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $email = 'verify-test@example.com';

        $this->post(route('register'), [
            'name' => 'Verify User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', $email)->firstOrFail();

        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** ② 認証リンクへアクセスできる */
    public function test_ユーザーは認証リンクにアクセスできる()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    /** ③ 認証完了後に勤怠画面へ遷移 */
    public function test_メール認証後にリダイレクトされる()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(60), [
            'id'   => $user->id,
            'hash' => sha1($user->email),
        ]);

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect(route('attendance.index', ['verified' => 1]));
    }
}
