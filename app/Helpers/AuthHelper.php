<?php

if (!function_exists('authUser')) {
    function authUser(bool $fresh = false) {
        static $cache;
        if ($fresh || $cache === null) {
            $id = session('auth_user_id');
            $cache = $id ? \App\Models\User::with('role')->find($id) : false;
        }
        return $cache ?: null;
    }
}

if (!function_exists('passwordFingerprint')) {
    /**
     * Short digest of a stored password hash, kept in the session so that
     * changing a password invalidates every *other* session for that account.
     * Deriving it from the hash avoids needing a column to track the change.
     */
    function passwordFingerprint(?string $passwordHash): string {
        return substr(hash('sha256', (string) $passwordHash), 0, 32);
    }
}

if (!function_exists('authRole')) {
    function authRole() { return session('auth_role'); }
}

if (!function_exists('isAdmin')) {
    function isAdmin() { return authRole() === 'System Administrator'; }
}

if (!function_exists('canAccess')) {
    function canAccess(string ...$roles): bool { return in_array(authRole(), $roles); }
}

if (!function_exists('requireRole')) {
    /**
     * Stops the request unless the current role is one of $roles.
     *
     * Livewire actions need this in the method itself: a component's mount()
     * runs only on the first render, so a guard there does not cover the
     * action requests that follow, and route middleware never sees them at all.
     */
    function requireRole(string ...$roles): void
    {
        if (!canAccess(...$roles)) {
            abort(403, 'Access denied.');
        }
    }
}

if (!function_exists('ROLES_STAFF')) {
    /**
     * Every role that may change data: both of them.
     *
     * This used to be four sets (ROLES_MANAGE / ENCODE / FIELD / CONTRIBUTE)
     * carving up six roles. The 2026-10 consolidation left two, which made all
     * four the same list, so they were collapsed into this one rather than kept
     * as aliases that would drift apart the first time someone "fixed" one.
     *
     * Deliberately not "anyone signed in": AuthMiddleware stores 'Unknown' when a
     * user's role relation is missing, so naming the two real roles keeps that
     * case failing closed.
     */
    function ROLES_STAFF(): array { return ['System Administrator', 'MENRO Staff']; }
}

if (!function_exists('logAudit')) {
    function logAudit(string $action, string $module, $recordId = null, $old = null, $new = null): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id'    => session('auth_user_id'),
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if audit log table not available
        }
    }
}
