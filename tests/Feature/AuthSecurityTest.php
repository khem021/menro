<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Exercises the sign-in and access guards through real HTTP requests, so the
 * session, CSRF and middleware stack are all in play.
 *
 * Writes run inside a transaction that is rolled back.
 */
class AuthSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD = 'admin123';

    /* ---------- Sign-in throttling ---------- */

    public function test_repeated_failures_lock_the_account_out(): void
    {
        $this->flushSession();

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['username' => 'admin', 'password' => 'wrong'])
                ->assertSessionHasErrors('login');

            $this->assertStringNotContainsString(
                'Too many sign-in attempts',
                $this->errorMessage(),
                "locked out early, on attempt {$i}",
            );
        }

        $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);

        $this->assertStringContainsString('Too many sign-in attempts', $this->errorMessage());
    }

    public function test_the_user_is_warned_before_being_locked_out(): void
    {
        $this->flushSession();

        for ($i = 1; $i <= 4; $i++) {
            $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $this->assertStringContainsString('attempt(s) remaining', $this->errorMessage());
    }

    public function test_lockout_does_not_leak_whether_the_username_exists(): void
    {
        $this->flushSession();

        $this->post('/login', ['username' => 'no-such-person', 'password' => 'wrong']);

        $this->assertStringContainsString('Invalid username or password', $this->errorMessage());
    }

    public function test_a_correct_password_still_works_below_the_limit(): void
    {
        $this->flushSession();

        $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);
        $this->post('/login', ['username' => 'admin', 'password' => self::PASSWORD])
            ->assertRedirect(route('dashboard'));
    }

    /* ---------- Admin-only routes ---------- */

    /**
     * @dataProvider adminRouteProvider
     */
    public function test_a_non_admin_is_refused_admin_routes(string $path): void
    {
        $this->signInAs('barangay');

        $this->get($path)->assertForbidden();
    }

    /**
     * @dataProvider adminRouteProvider
     */
    public function test_an_administrator_reaches_admin_routes(string $path): void
    {
        $this->signInAs('admin');

        $this->get($path)->assertOk();
    }

    /** @return array<string, array{string}> */
    public static function adminRouteProvider(): array
    {
        return [
            'users' => ['/users'],
            'audit' => ['/audit'],
            'archive' => ['/archive'],
        ];
    }

    /**
     * The admin guard must not have narrowed anything else. Note that /reports
     * is deliberately excluded: ReportIndex restricts itself to System
     * Administrator and MENRO Officer, which predates the route middleware.
     */
    public function test_a_non_admin_still_reaches_shared_pages(): void
    {
        $this->signInAs('barangay');

        $this->get('/dashboard')->assertOk();
        $this->get('/settings')->assertOk();
        $this->get('/entries')->assertOk();
        $this->get('/generators')->assertOk();
    }

    /* ---------- Session re-validation ---------- */

    public function test_deactivating_a_user_ends_their_session(): void
    {
        $user = $this->signInAs('barangay');
        $this->get('/dashboard')->assertOk();

        $user->update(['status' => 'inactive']);

        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_a_role_change_applies_without_signing_in_again(): void
    {
        $user = $this->signInAs('barangay');
        $this->get('/users')->assertForbidden();

        $user->update(['role_id' => Role::where('role_name', 'System Administrator')->value('role_id')]);

        // Same session, no second sign-in.
        $this->get('/users')->assertOk();
    }

    public function test_changing_a_password_ends_other_sessions(): void
    {
        $user = $this->signInAs('barangay');
        $this->get('/dashboard')->assertOk();

        // Stands in for the same account signed in elsewhere changing its
        // password: this session still holds the old fingerprint.
        $user->update(['password_hash' => bcrypt('a-different-password')]);

        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_a_session_predating_the_check_is_adopted_not_dropped(): void
    {
        $user = User::where('username', 'barangay')->firstOrFail();

        // No auth_pw key, as with a session created before the check existed.
        $this->flushSession();
        $this->withSession([
            'auth_user_id' => $user->user_id,
            'auth_role'    => 'Barangay User',
        ]);

        $this->get('/dashboard')->assertOk();
    }

    /* ---------- Helpers ---------- */

    private function signInAs(string $username): User
    {
        $user = User::where('username', $username)->firstOrFail();

        $this->flushSession();
        $this->withSession([
            'auth_user_id' => $user->user_id,
            'auth_role'    => $user->role->role_name ?? 'Unknown',
            'auth_pw'      => passwordFingerprint($user->password_hash),
        ]);

        return $user;
    }

    private function errorMessage(): string
    {
        $errors = session('errors');

        return $errors ? implode(' ', $errors->get('login')) : '';
    }
}
