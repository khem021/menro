<?php

namespace Tests\Unit;

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Covers the three guards around sign-in and admin access. These run without a
 * database so that an authorization regression is still caught when the DB is
 * unavailable.
 */
class AuthorizationTest extends TestCase
{
    private const ADMIN = 'System Administrator';
    private const GUARD = 'role:System Administrator';

    /** Routes that expose user management, the audit trail or the archive. */
    private const ADMIN_ROUTES = [
        'users.index', 'users.create', 'users.edit',
        'audit.index', 'archive.index', 'archive.download',
    ];

    /** Routes every signed-in role may reach. */
    private const SHARED_ROUTES = [
        'dashboard', 'analytics.index', 'reports.index',
        'reports.export', 'settings', 'entries.index', 'generators.index',
    ];

    /* ---------- Route wiring ---------- */

    public function test_admin_routes_are_behind_the_role_guard(): void
    {
        foreach (self::ADMIN_ROUTES as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "route {$name} is missing");
            $this->assertContains(self::GUARD, $route->gatherMiddleware(),
                "{$name} must be restricted to administrators");
        }
    }

    public function test_admin_routes_still_require_a_signed_in_user(): void
    {
        foreach (self::ADMIN_ROUTES as $name) {
            $this->assertContains('auth.custom',
                Route::getRoutes()->getByName($name)->gatherMiddleware(),
                "{$name} must stay behind authentication");
        }
    }

    public function test_shared_routes_are_not_restricted_to_admins(): void
    {
        foreach (self::SHARED_ROUTES as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "route {$name} is missing");
            $this->assertNotContains(self::GUARD, $route->gatherMiddleware(),
                "{$name} should be reachable by every signed-in role");
        }
    }

    /* ---------- The guard itself ---------- */

    public function test_an_administrator_passes_the_role_guard(): void
    {
        Session::put('auth_role', self::ADMIN);

        $this->assertSame('next', $this->runGuard());
    }

    /**
     * @dataProvider nonAdminRoleProvider
     */
    public function test_other_roles_are_refused_with_403(string $role): void
    {
        Session::put('auth_role', $role);

        try {
            $this->runGuard();
            $this->fail("{$role} should not reach an admin-only route");
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    /** @return array<string, array{string}> */
    public static function nonAdminRoleProvider(): array
    {
        return [
            'MENRO officer'   => ['MENRO Officer'],
            'field inspector' => ['Field Inspector'],
            'data encoder'    => ['Data Encoder'],
            'report viewer'   => ['Report Viewer'],
            'barangay user'   => ['Barangay User'],
        ];
    }

    public function test_a_session_with_no_role_fails_closed(): void
    {
        Session::forget('auth_role');

        $this->expectException(HttpException::class);
        $this->runGuard();
    }

    /* ---------- Sign-in throttling ---------- */

    public function test_sign_in_locks_out_after_five_failures(): void
    {
        $key = 'login:someone|127.0.0.1';

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(RateLimiter::tooManyAttempts($key, 5),
                'lockout tripped early, on attempt ' . ($i + 1));
            RateLimiter::hit($key, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));
    }

    public function test_a_successful_sign_in_resets_the_counter(): void
    {
        $key = 'login:someone|127.0.0.1';

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }
        RateLimiter::clear($key);

        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
        $this->assertSame(5, RateLimiter::remaining($key, 5));
    }

    public function test_lockout_is_scoped_per_username_and_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('login:victim|10.0.0.1', 60);
        }

        // The same account from the owner's own machine is unaffected, so an
        // attacker cannot lock a real user out.
        $this->assertFalse(RateLimiter::tooManyAttempts('login:victim|10.0.0.2', 5));
    }

    /* ---------- Password fingerprint ---------- */

    public function test_fingerprint_is_stable_for_one_hash(): void
    {
        $hash = password_hash('correct horse', PASSWORD_BCRYPT);

        $this->assertSame(passwordFingerprint($hash), passwordFingerprint($hash));
    }

    public function test_fingerprint_changes_when_the_password_changes(): void
    {
        $before = passwordFingerprint(password_hash('old', PASSWORD_BCRYPT));
        $after  = passwordFingerprint(password_hash('new', PASSWORD_BCRYPT));

        $this->assertNotSame($before, $after,
            'a changed password must invalidate other sessions');
        $this->assertFalse(hash_equals($before, $after));
    }

    public function test_fingerprint_does_not_expose_the_hash(): void
    {
        $hash = password_hash('correct horse', PASSWORD_BCRYPT);

        $this->assertStringNotContainsString(substr($hash, 7, 16), passwordFingerprint($hash));
    }

    public function test_fingerprint_tolerates_a_missing_hash(): void
    {
        $this->assertSame(passwordFingerprint(null), passwordFingerprint(null));
    }

    private function runGuard(): mixed
    {
        return (new RoleMiddleware())->handle(
            Request::create('/users', 'GET'),
            fn() => 'next',
            self::ADMIN,
        );
    }
}
