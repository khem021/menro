<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('auth_user_id')) {
            return $this->signedOut($request);
        }

        // Re-read the account on every request. Without this, a user who is
        // deactivated or deleted keeps a working session until they log out,
        // and a role change does not take effect until the next sign-in.
        // authUser() memoises the result, so the rest of the request reuses it
        // rather than querying again.
        $user = authUser(true);

        if (!$user || $user->status !== 'active') {
            session()->flush();
            session()->regenerate();

            return $this->signedOut($request, 'Your account is no longer active. Please contact an administrator.');
        }

        // A changed password ends every other session for that account. The
        // session that performed the change refreshes its own fingerprint, so
        // only the others are signed out.
        $fingerprint = passwordFingerprint($user->password_hash);

        if (!session()->has('auth_pw')) {
            // Session predates this check — adopt the current password rather
            // than signing everyone out on deploy.
            session(['auth_pw' => $fingerprint]);
        } elseif (!hash_equals(session('auth_pw'), $fingerprint)) {
            session()->flush();
            session()->regenerate();

            return $this->signedOut($request, 'Your password was changed. Please sign in again.');
        }

        $role = $user->role->role_name ?? 'Unknown';

        if (session('auth_role') !== $role) {
            session(['auth_role' => $role]);
        }

        return $next($request);
    }

    /**
     * A normal page goes back to the login screen. A Livewire request cannot
     * follow a redirect, so it gets a 419, which makes Livewire reload the page
     * (and that reload lands on the login screen).
     */
    private function signedOut(Request $request, ?string $message = null)
    {
        if ($request->headers->has('X-Livewire')) {
            abort(419, $message ?? 'Your session has ended.');
        }

        $redirect = redirect()->route('login');

        return $message ? $redirect->withErrors(['login' => $message]) : $redirect;
    }
}
