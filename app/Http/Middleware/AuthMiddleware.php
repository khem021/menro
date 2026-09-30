<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('auth_user_id')) {
            return redirect()->route('login');
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

            return redirect()->route('login')
                ->withErrors(['login' => 'Your account is no longer active. Please contact an administrator.']);
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

            return redirect()->route('login')
                ->withErrors(['login' => 'Your password was changed. Please sign in again.']);
        }

        $role = $user->role->role_name ?? 'Unknown';

        if (session('auth_role') !== $role) {
            session(['auth_role' => $role]);
        }

        return $next($request);
    }
}
