<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
         Fortify::registerView(function () {
         return view('auth.register');
     });

     Fortify::loginView(function (Request $request) {
        if (Route::currentRouteNamed('admin.login')) { // 管理者ログインルートの場合
            return view('auth.admin');
        }
        return view('auth.login');
     });

     Fortify::authenticateUsing(function (Request $request) {
        $credentials = $request->only(Fortify::username(), 'password');

        // 現在のルートが 'admin.login' ならば admin ガードで認証を試みる
        if (Route::currentRouteNamed('admin.login')) {
            if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
                return Auth::guard('admin')->user(); // 認証成功したらユーザーを返す
            }
        } else {
            // それ以外のログインルート（一般ユーザーログイン）ならば web ガードで認証を試みる
            if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
                return Auth::guard('web')->user(); // 認証成功したらユーザーを返す
            }
        }

        return null; // 認証失敗
    });

     RateLimiter::for('login', function (Request $request) {
         $email = (string) $request->email;

         return Limit::perMinute(10)->by($email . $request->ip());
     });

     Fortify::verifyEmailView(function () {
        return view('auth.verify-email');
     });
    }
}
