<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    #[Test]
    public function メール未入力でログイン失敗()
    {
        $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ])
        ->assertSessionHasErrors('email');
    }

    #[Test]
    public function パスワード未入力でログイン失敗()
    {
        $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => '',
        ])
        ->assertSessionHasErrors('password');
    }

    #[Test]
    public function 登録内容と一致しない場合ログイン失敗()
    {
        $this->createAdmin();

        $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpass',
        ])
        ->assertSessionHasErrors();
    }

    #[Test]
    public function 管理者は正常ログインできる()
    {
        $admin = $this->createAdmin();

        $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ])
        ->assertRedirect(route('admin.attendance.list'));

        $this->assertAuthenticatedAs($admin);
    }
}
