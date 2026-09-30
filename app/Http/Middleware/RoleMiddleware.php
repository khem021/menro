<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Restricts a route to the given roles.
 *
 *     Route::get('/users', ...)->middleware('role:System Administrator');
 *
 * Admin-only pages already re-check with isAdmin() inside the component, and
 * should keep doing so — this runs first so that access is settled by the route
 * definition rather than depending on every future component remembering to
 * guard its own mount().
 *
 * Runs after auth.custom, which is what puts the current role in the session.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!canAccess(...$roles)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
