<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AdminLoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin/attendance/list';

    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.admin');
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    // public function login(Request $request): LoginResponse
    // {
    //     $this->validateLogin($request);

    //     if ($this->attemptLogin($request)) {
    //         $request->session()->regenerate();

    //         return app(LoginResponse::class);
    //     }

    //     $this->incrementLoginAttempts($request);
    //     $this->sendFailedLoginResponse($request);
    // }

    // protected function attemptLogin(Request $request)
    // {
    //     return Auth::guard()->attempt(
    //         $this->credentials($request),
    //         $request->filled('remember')
    //     );
    //     $loggedIn = Auth::guard('admin')->attempt(
    //         $credentials,
    //         $remember
    //     );

    //     // ★ここで認証結果を確認
    //     dd($loggedIn, $credentials); // 認証成功ならtrue、失敗ならfalseとクレデンシャルが表示される

    //     return $loggedIn;
    // }

    protected function credentials(Request $request)
    {
        return $request->only(Fortify::username(), 'password');
    }

    protected function authenticated(Request $request, $user)
    {
        // $this->redirectTo が設定されているので、そこにリダイレクト
        return redirect()->intended($this->redirectTo);
    }

    // protected function sendFailedLoginResponse(Request $request)
    // {
    //     throw ValidationException::withMessages([
    //         Fortify::username() => [trans('auth.failed')],
    //     ]);
    // }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }


    // protected function validateLogin(Request $request)
    // {
    //     $request->validate([
    //         Fortify::username() => 'required|string',
    //         'password' => 'required|string',
    //     ]);
    // }

    // protected function incrementLoginAttempts(Request $request)
    // {
    //     $this->limiter()->hit(
    //         $request->input(Fortify::username()) . '|' . $request->ip(),
    //         $this->decayMinutes()
    //     );
    // }

    // protected function limiter()
    // {
    //     return app(\Illuminate\Cache\RateLimiter::class);
    // }

    // protected function decayMinutes()
    // {
    //     return 1; // 例：試行回数を1分間保持
    // }

    // protected function sendLockoutResponse(Request $request)
    // {
    //     $seconds = $this->limiter()->availableIn(
    //         $this->throttleKey($request)
    //     );

    //     throw ValidationException::withMessages([
    //         Fortify::username() => [trans('auth.throttle', ['seconds' => $seconds])],
    //     ])->status(429);
    // }

    // protected function throttleKey(Request $request)
    // {
    //     return Str::lower($request->input(Fortify::username())) . '|' . $request->ip();
    // }
}
