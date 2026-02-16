<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;

use App\Http\Requests\LoginRequest;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * サービス登録
     */
    public function register(): void
    {
        //
    }

    /**
     * Fortify設定（超重要）
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ユーザー作成・更新系
        |--------------------------------------------------------------------------
        */
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(
            RedirectIfTwoFactorAuthenticatable::class
        );


        /*
        |--------------------------------------------------------------------------
        | 画面指定（Blade）
        |--------------------------------------------------------------------------
        */
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });


        /*
        |        |--------------------------------------------------------------------------
        | ★ 登録後のリダイレクト先変更
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {

                public function toResponse($request)
                {
                    return redirect()->route('login');
                }

            };
        });


        /*
        |--------------------------------------------------------------------------
        | ログイン試行制限
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('login', function (Request $request) {

            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });


        /*
        |--------------------------------------------------------------------------
        | 2段階認証制限
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('two-factor', function (Request $request) {

            return Limit::perMinute(5)
                ->by($request->session()->get('login.id'));
        });

        $this->app->singleton(LogoutResponse::class, function () {
    return new class implements LogoutResponse {
        public function toResponse($request)
        {
            return redirect()->route('login');
        }
    };
});

    }
}
