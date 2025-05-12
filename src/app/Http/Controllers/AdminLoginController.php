<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AdminLoginController extends Controller
{

    protected $redirectTo = '/admin/attendance/list';

    public function showLoginForm()
    {
        return view('auth.admin');
    }

    public function login(Request $request): LoginResponse
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $request->session()->regenerate();

            return app(LoginResponse::class);
        }

        $this->incrementLoginAttempts($request);

        $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        return Auth::guard()->attempt(
            $this->credentials($request) + ['is_admin' => true],
            $request->filled('remember')
        );
    }

    protected function credentials(Request $request)
    {
        return $request->only(Fortify::username(), 'password');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }


protected function validateLogin(Request $request)
    {
        $request->validate([
            Fortify::username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $this->limiter()->hit(
            $request->input(Fortify::username()) . '|' . $request->ip(),
            $this->decayMinutes()
        );
    }

    protected function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }

    protected function decayMinutes()
    {
        return 1; // 例：試行回数を1分間保持
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.throttle', ['seconds' => $seconds])],
        ])->status(429);
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input(Fortify::username())) . '|' . $request->ip();
    }
}
