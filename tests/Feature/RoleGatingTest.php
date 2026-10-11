<?php

namespace Tests\Feature;

use App\Http\Livewire\Entries\EntryForm;
use App\Http\Livewire\Incidents\IncidentForm;
use App\Http\Livewire\Users\UserForm;
use App\Http\Livewire\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * What the two roles may do.
 *
 * The 2026-10 consolidation left System Administrator and MENRO Staff, so the
 * only role boundary still in the app is user management and the audit trail.
 * The tests that used to assert "a Report Viewer cannot encode" or
 * "a Barangay User cannot delete" were removed with the roles they named.
 *
 * The action-level tests matter most, and that has not changed: a component's
 * mount() runs only on the first render, and Livewire's action requests never
 * pass through the route middleware, so a guard in either place alone leaves the
 * action open.
 */
class RoleGatingTest extends TestCase
{
    use DatabaseTransactions;

    private function as(string $username): User
    {
        $u = User::where('username', $username)->firstOrFail();
        $this->flushSession();
        $state = [
            'auth_user_id' => $u->user_id,
            'auth_role'    => $u->role->role_name,
            'auth_pw'      => passwordFingerprint($u->password_hash),
        ];
        session($state);
        $this->withSession($state);

        return $u;
    }

    /** Runs a Livewire action and returns the status it aborted with, or null if it ran. */
    private function actionStatus(string $class, string $method, array $props = []): ?int
    {
        try {
            $c = Livewire::test($class);
            foreach ($props as $k => $v) {
                $c->set($k, $v);
            }
            DB::transaction(fn () => $c->call($method));

            $e = $c->lastResponse->exception ?? null;
            if ($e instanceof HttpException) {
                return $e->getStatusCode();
            }
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }

        return null;
    }

    // -- Pages ---------------------------------------------------------------

    public function test_menro_staff_can_open_the_entry_form(): void
    {
        $this->as('menro');
        $this->get('/entries/create')->assertOk();
    }

    public function test_menro_staff_can_report_an_incident(): void
    {
        $this->as('menro');
        $this->get('/incidents/create')->assertOk();
    }

    public function test_menro_staff_can_read_the_dashboard_and_entries(): void
    {
        $this->as('menro');
        $this->get('/dashboard')->assertOk();
        $this->get('/entries')->assertOk();
    }

    // -- Actions (these bypass route middleware entirely) ---------------------

    /**
     * The admin pages are the only role boundary left, so this is where the
     * "a Livewire action must guard itself" rule now has to be proven.
     */
    public function test_the_user_save_action_rejects_staff_even_without_the_route(): void
    {
        $this->as('menro');

        // Called on the component directly, not through Livewire's harness: a
        // non-admin cannot mount UserForm at all, so the harness never builds a
        // payload to call an action with. This asserts the guard inside save()
        // itself, which is the layer that matters if a payload ever leaked.
        $status = null;
        try {
            DB::transaction(fn () => (new UserForm())->save());
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
        }

        $this->assertSame(403, $status, 'MENRO Staff must not be able to save a user.');
    }

    /**
     * A session minted before the consolidation still carries a role name that
     * no longer exists. AuthMiddleware refreshes it on a full page request, but
     * a Livewire action must refuse it rather than fall open.
     */
    public function test_a_session_holding_a_retired_role_name_cannot_write(): void
    {
        $u = User::where('username', 'menro')->firstOrFail();

        $this->flushSession();
        $state = [
            'auth_user_id' => $u->user_id,
            'auth_role'    => 'Data Encoder',
            'auth_pw'      => passwordFingerprint($u->password_hash),
        ];
        session($state);
        $this->withSession($state);

        $this->assertSame(403, $this->actionStatus(EntryForm::class, 'save'));
        $this->assertSame(403, $this->actionStatus(IncidentForm::class, 'save'));
    }

    /**
     * Called on the component directly, not through Livewire's harness: a
     * non-admin cannot mount UserIndex at all, so the harness never builds a
     * payload to call an action with. This asserts the guard inside delete()
     * itself, which is the layer that would matter if a payload ever leaked or
     * a role were downgraded mid-session.
     */
    public function test_the_user_delete_action_guards_itself_against_a_non_admin(): void
    {
        $this->as('menro');

        $victim = User::where('username', 'viewer')->firstOrFail();

        $status = null;
        try {
            DB::transaction(fn () => (new UserIndex())->delete($victim->user_id));
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
        }

        $this->assertSame(403, $status, 'A MENRO Staff must not be able to delete a user.');
        $this->assertDatabaseHas('users', ['user_id' => $victim->user_id]);
    }

    public function test_a_non_admin_cannot_even_open_the_users_page(): void
    {
        $this->as('menro');
        $this->get('/users')->assertForbidden();
    }
}
