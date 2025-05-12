<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AttemptToAuthenticate
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new class instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Attempt to authenticate the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request): Authenticatable
    {
        $this->ensureIsNotRateLimited($request);

        if (! $this->attempt($request)) {
            $this->throwFailedAuthenticationException($request);
        }

        return $this->guard->getLastAttempted();
    }

    /**
     * Attempt to authenticate the request via the guard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attempt(Request $request): bool
    {
        return $this->guard->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request): array
    {
        return $request->only(Fortify::username(), 'password');
    }

    /**
     * Ensure that the request is not rate limited.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! Fortify::hasTooManyLoginAttempts($request)) {
            return;
        }

        Fortify::hitLoginRateLimiter($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.throttle', [
                'seconds' => cache()->get(Fortify::RATE_LIMITER_CACHE_KEY . ':' . $request->ip()) - time(),
            ])],
        ]);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request): void
    {
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard(); // デフォルトの 'web' ガードを使用
    }

    /**
     * Redirect the user after authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResponse(Request $request)
    {
        $user = Auth::user();

        if ($user->is_admin) {
            return redirect()->intended(route('admin.attendance.list'));
        }

        return redirect()->intended(route('attendance'));
    }
}
