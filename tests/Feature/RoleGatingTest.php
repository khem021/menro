<?php

namespace Tests\Feature;

use App\Http\Livewire\Entries\EntryForm;
use App\Http\Livewire\Incidents\IncidentForm;
use App\Http\Livewire\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Report Viewer is "read-only access to reports and dashboards", but the create
 * routes carried no role middleware and the forms only checked authorization
 * when editing — so any signed-in role could encode waste entries and report
 * incidents.
 *
 * The action-level tests matter most: a component's mount() runs only on the
 * first render, and Livewire's action requests never pass through the route
 * middleware, so a guard in either place alone leaves the action open.
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

    // ── Pages ────────────────────────────────────────────────────────────────

    public function test_a_report_viewer_cannot_open_the_entry_form(): void
    {
        $this->as('viewer');
        $this->get('/entries/create')->assertForbidden();
    }

    public function test_a_report_viewer_cannot_open_the_incident_form(): void
    {
        $this->as('viewer');
        $this->get('/incidents/create')->assertForbidden();
    }

    public function test_an_encoder_can_still_open_the_entry_form(): void
    {
        $this->as('encoder');
        $this->get('/entries/create')->assertOk();
    }

    public function test_a_barangay_user_can_still_report_an_incident(): void
    {
        $this->as('barangay');
        $this->get('/incidents/create')->assertOk();
    }

    public function test_a_barangay_user_cannot_encode_waste_entries(): void
    {
        $this->as('barangay');
        $this->get('/entries/create')->assertForbidden();
    }

    public function test_a_report_viewer_can_still_read_the_dashboard(): void
    {
        $this->as('viewer');
        $this->get('/dashboard')->assertOk();
        $this->get('/entries')->assertOk();
    }

    // ── Actions (these bypass route middleware entirely) ─────────────────────

    public function test_the_save_action_rejects_a_report_viewer_even_without_the_route(): void
    {
        $this->as('viewer');

        $this->assertSame(403, $this->actionStatus(EntryForm::class, 'save'));
        $this->assertSame(403, $this->actionStatus(IncidentForm::class, 'save'));
    }

    public function test_a_barangay_user_cannot_reach_the_entry_save_action(): void
    {
        $this->as('barangay');

        $this->assertSame(403, $this->actionStatus(EntryForm::class, 'save'));
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

        $this->assertSame(403, $status, 'A MENRO Officer must not be able to delete a user.');
        $this->assertDatabaseHas('users', ['user_id' => $victim->user_id]);
    }

    public function test_a_non_admin_cannot_even_open_the_users_page(): void
    {
        $this->as('menro');
        $this->get('/users')->assertForbidden();
    }
}
