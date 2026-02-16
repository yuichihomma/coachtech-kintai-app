<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者
        User::create([
            'name' => '管理者',
            'email' => 'admin@coachtech.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // スタッフ
        $staffs = [
            ['西 怜奈', 'reina@coachtech.com'],
            ['山田 太郎', 'taro@coachtech.com'],
            ['増田 一生', 'issei@coachtech.com'],
            ['山本 啓', 'keikichi@coachtech.com'],
            ['秋田 明美', 'tomomi@coachtech.com'],
            ['中西 俊夫', 'norio@coachtech.com'],
        ];

        foreach ($staffs as [$name, $email]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]);
        }
    }
}
