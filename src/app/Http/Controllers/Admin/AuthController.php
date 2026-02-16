<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AdminLoginRequest;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(AdminLoginRequest $request)
{
    $credentials = $request->validated();

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        if (auth()->user()->role !== 'admin') {
            Auth::logout();
            return back()->withErrors([
                'email' => '管理者権限がありません。',
            ]);
        }

        return redirect('/admin/attendance/list');
    }

    return back()->withErrors([
        'email' => 'ログイン情報が正しくありません。',
    ]);
}

}
