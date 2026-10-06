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

if (!function_exists('ROLES_MANAGE')) {
    /** Roles allowed to manage reference data and collections. */
    function ROLES_MANAGE(): array { return ['System Administrator', 'MENRO Officer']; }
}

if (!function_exists('ROLES_ENCODE')) {
    /** Roles allowed to encode waste entries. */
    function ROLES_ENCODE(): array { return ['System Administrator', 'MENRO Officer', 'Data Encoder']; }
}

if (!function_exists('ROLES_FIELD')) {
    /** Roles allowed to record inspections, violations and tickets. */
    function ROLES_FIELD(): array { return ['System Administrator', 'MENRO Officer', 'Field Inspector']; }
}

if (!function_exists('ROLES_CONTRIBUTE')) {
    /** Everyone except Report Viewer, which is read-only by definition. */
    function ROLES_CONTRIBUTE(): array
    {
        return ['System Administrator', 'MENRO Officer', 'Data Encoder', 'Field Inspector', 'Barangay User'];
    }
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
