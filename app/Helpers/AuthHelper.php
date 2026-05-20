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

if (!function_exists('authRole')) {
    function authRole() { return session('auth_role'); }
}

if (!function_exists('isAdmin')) {
    function isAdmin() { return authRole() === 'System Administrator'; }
}

if (!function_exists('canAccess')) {
    function canAccess(string ...$roles): bool { return in_array(authRole(), $roles); }
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
