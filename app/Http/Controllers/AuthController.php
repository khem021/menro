<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Failed sign-ins allowed per username+IP before the form locks out. */
    private const MAX_ATTEMPTS = 5;

    /** How long the lockout lasts, in seconds. */
    private const DECAY_SECONDS = 60;

    public function redirectRoot()
    {
        return session('auth_user_id')
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    public function showLogin()
    {
        if (session('auth_user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = $this->throttleKey($request);

        // Lock the form after repeated failures so the sign-in page cannot be
        // used to guess passwords.
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['login' => "Too many sign-in attempts. Please try again in {$seconds} seconds."])
                ->withInput(['username' => $request->username]);
        }

        $user = User::with('role')
            ->where('username', $request->username)
            ->where('status', 'active')
            ->first();

        if (!$user || !password_verify($request->password, $user->password_hash)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            $remaining = RateLimiter::remaining($key, self::MAX_ATTEMPTS);
            $message   = 'Invalid username or password.';

            // Warn before the lockout lands rather than after, so a user who is
            // simply mistyping is not locked out without notice.
            if ($remaining > 0 && $remaining <= 2) {
                $message .= " {$remaining} attempt(s) remaining before this account is temporarily locked.";
            }

            return back()->withErrors(['login' => $message])->withInput(['username' => $request->username]);
        }

        RateLimiter::clear($key);

        session()->regenerate();

        session([
            'auth_user_id' => $user->user_id,
            'auth_role'    => $user->role->role_name ?? 'Unknown',
            'auth_pw'      => passwordFingerprint($user->password_hash),
        ]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        session()->regenerate();
        return redirect()->route('login');
    }

    /**
     * Rate-limit bucket for a sign-in attempt. Keying on username *and* IP means
     * an attacker hammering one account cannot lock its real owner out from a
     * different machine.
     */
    private function throttleKey(Request $request): string
    {
        return 'login:' . Str::lower((string) $request->input('username')) . '|' . $request->ip();
    }
}
