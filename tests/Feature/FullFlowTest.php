<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * End-to-end functional sweep: login -> every module (render + create + edit +
 * validation + delete) -> logout. All writes run inside a transaction that is
 * rolled back, so the dev database is left untouched.
 */
class FullFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Custom session-based auth (see App\Http\Middleware\AuthMiddleware)
        $this->withSession([
            'auth_user_id' => 1,
            'auth_role'    => 'System Administrator',
        ]);
        session(['auth_user_id' => 1, 'auth_role' => 'System Administrator']);

        $this->ids = [
            'barangay'       => (int) \DB::table('barangays')->min('barangay_id'),
            'generator_type' => (int) \DB::table('generator_types')->min('generator_type_id'),
            'category'       => (int) \DB::table('waste_categories')->min('category_id'),
            'generator'      => (int) \DB::table('waste_generators')->min('generator_id'),
            'inspection'     => (int) \DB::table('inspections')->min('inspection_id'),
            'violation'      => (int) \DB::table('violations')->min('violation_id'),
            'incident'       => (int) \DB::table('incidents')->min('incident_id'),
            'entry'          => (int) \DB::table('waste_entries')->min('entry_id'),
            'schedule'       => (int) \DB::table('collection_schedules')->min('schedule_id'),
            'role'           => (int) \DB::table('roles')->min('role_id'),
        ];
    }

    /* ---------- Auth ---------- */

    public function test_login_page_renders(): void
    {
        $this->flushSession();
        $this->get('/login')->assertOk()->assertSee('MENRO');
    }

    public function test_login_with_valid_credentials(): void
    {
        $this->flushSession();
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_with_bad_credentials_fails(): void
    {
        $this->flushSession();
        $this->post('/login', ['username' => 'admin', 'password' => 'wrong'])
            ->assertSessionHasErrors('login');
    }

    public function test_logout_clears_session_and_guards_kick_in(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));
        $this->flushSession();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    /* ---------- Controller pages ---------- */

    public function test_controller_pages_load(): void
    {
        foreach (['dashboard', 'analytics.index'] as $name) {
            $this->get(route($name))->assertOk();
        }
        foreach (['monthly_waste', 'compliance_summary', 'incident_summary', 'collection_summary'] as $type) {
            $this->get(route('reports.print', ['type' => $type]))->assertOk();
            $this->get(route('reports.export', ['type' => $type]))->assertOk();
        }
    }

    public function test_dashboard_shows_reorganized_sections(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Waste Volume — Last 12 Months')
            ->assertSee('Upcoming Collections')
            ->assertSee('Recent Incidents')
            ->assertDontSee('Cluster Configuration'); // moved off the dashboard
    }

    public function test_cluster_config_page_moved_to_barangays(): void
    {
        $this->get(route('clusters.index'))->assertOk()->assertSee('Barangay Clusters');

        // guests are bounced
        $this->flushSession();
        $this->get(route('clusters.index'))->assertRedirect(route('login'));
    }

    public function test_cluster_assign_and_unassign(): void
    {
        $b = \DB::table('barangays')->orderBy('barangay_id')->first();
        $original = $b->cluster;

        Livewire::test(\App\Http\Livewire\Dashboard\ClusterConfig::class)
            ->set('new1', $b->barangay_name)
            ->call('addToCluster', 1);
        $this->assertSame(1, (int) \DB::table('barangays')->where('barangay_id', $b->barangay_id)->value('cluster'));

        Livewire::test(\App\Http\Livewire\Dashboard\ClusterConfig::class)
            ->call('removeFromCluster', $b->barangay_id);
        $this->assertNull(\DB::table('barangays')->where('barangay_id', $b->barangay_id)->value('cluster'));

        // restore (belt-and-braces; transaction rolls back anyway)
        \DB::table('barangays')->where('barangay_id', $b->barangay_id)->update(['cluster' => $original]);
    }

    /* ---------- Livewire index screens ---------- */

    public function test_all_index_components_render(): void
    {
        $components = [
            \App\Http\Livewire\Generators\GeneratorIndex::class,
            \App\Http\Livewire\Entries\EntryIndex::class,
            \App\Http\Livewire\Compliance\ComplianceIndex::class,
            \App\Http\Livewire\Inspections\InspectionIndex::class,
            \App\Http\Livewire\Violations\ViolationIndex::class,
            \App\Http\Livewire\Barangays\BarangayIndex::class,
            \App\Http\Livewire\Collections\CollectionIndex::class,
            \App\Http\Livewire\Incidents\IncidentIndex::class,
            \App\Http\Livewire\Notifications\NotificationIndex::class,
            \App\Http\Livewire\Users\UserIndex::class,
            \App\Http\Livewire\Reports\ReportIndex::class,
            \App\Http\Livewire\Audit\AuditIndex::class,
            \App\Http\Livewire\Settings\SettingsForm::class,
        ];

        foreach ($components as $c) {
            Livewire::test($c)->assertOk();
        }
    }

    public function test_index_search_and_filter_do_not_error(): void
    {
        Livewire::test(\App\Http\Livewire\Generators\GeneratorIndex::class)
            ->set('search', 'a')->set('status', 'active')->set('compliance_status', 'compliant')
            ->assertOk();

        Livewire::test(\App\Http\Livewire\Entries\EntryIndex::class)
            ->set('search', 'a')->set('date_from', '2020-01-01')->set('date_to', '2030-01-01')
            ->assertOk();

        Livewire::test(\App\Http\Livewire\Violations\ViolationIndex::class)
            ->set('severity', 'high')->set('resolution_status', 'open')->assertOk();
    }

    public function test_generator_search_is_case_insensitive(): void
    {
        $name = \DB::table('waste_generators')->value('generator_name');
        if (!$name) {
            $this->markTestSkipped('no generators seeded');
        }

        $lower = Livewire::test(\App\Http\Livewire\Generators\GeneratorIndex::class)
            ->set('search', strtolower($name))->viewData('generators');
        $upper = Livewire::test(\App\Http\Livewire\Generators\GeneratorIndex::class)
            ->set('search', strtoupper($name))->viewData('generators');

        $this->assertGreaterThan(0, $lower->total());
        $this->assertSame($lower->total(), $upper->total());
    }

    /* ---------- Generators CRUD ---------- */

    public function test_generator_create_edit_validate_delete(): void
    {
        // validation
        Livewire::test(\App\Http\Livewire\Generators\GeneratorForm::class)
            ->call('save')
            ->assertHasErrors(['generator_name', 'generator_type_id', 'barangay_id']);

        // create
        Livewire::test(\App\Http\Livewire\Generators\GeneratorForm::class)
            ->set('generator_name', 'TEST Generator')
            ->set('generator_type_id', (string) $this->ids['generator_type'])
            ->set('barangay_id', (string) $this->ids['barangay'])
            ->set('estimated_daily_waste_kg', '12.5')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('generators.index'));

        $id = \DB::table('waste_generators')->where('generator_name', 'TEST Generator')->value('generator_id');
        $this->assertNotNull($id);

        // edit
        Livewire::test(\App\Http\Livewire\Generators\GeneratorForm::class, ['id' => $id])
            ->assertSet('generator_name', 'TEST Generator')
            ->set('generator_name', 'TEST Generator 2')
            ->call('save')
            ->assertHasNoErrors();

        // delete
        Livewire::test(\App\Http\Livewire\Generators\GeneratorIndex::class)
            ->call('delete', $id);
        $this->assertDatabaseMissing('waste_generators', ['generator_id' => $id]);
    }

    /* ---------- Waste entries CRUD ---------- */

    public function test_entry_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Entries\EntryForm::class)
            ->set('quantity', '')->call('save')
            ->assertHasErrors(['generator_id', 'category_id', 'quantity']);

        Livewire::test(\App\Http\Livewire\Entries\EntryForm::class)
            ->set('generator_id', (string) $this->ids['generator'])
            ->set('category_id', (string) $this->ids['category'])
            ->set('quantity', '5')
            ->set('unit', 'kg')
            ->set('entry_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('entries.index'));

        $id = \DB::table('waste_entries')->where('quantity', 5)->orderByDesc('entry_id')->value('entry_id');
        $this->assertNotNull($id);

        Livewire::test(\App\Http\Livewire\Entries\EntryForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Entries\EntryIndex::class)->call('delete', $id);
        $this->assertDatabaseMissing('waste_entries', ['entry_id' => $id]);
    }

    /* ---------- Inspections CRUD ---------- */

    public function test_inspection_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Inspections\InspectionForm::class)
            ->call('save')
            ->assertHasErrors(['generator_id']);

        Livewire::test(\App\Http\Livewire\Inspections\InspectionForm::class)
            ->set('generator_id', (string) $this->ids['generator'])
            ->set('inspection_date', now()->format('Y-m-d'))
            ->set('inspector_id', '1')
            ->set('compliance_status', 'compliant')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('inspections.index'));

        $id = \DB::table('inspections')->orderByDesc('inspection_id')->value('inspection_id');
        Livewire::test(\App\Http\Livewire\Inspections\InspectionForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Inspections\InspectionIndex::class)->call('delete', $id);
        $this->assertSoftDeleted('inspections', ['inspection_id' => $id]);
    }

    /* ---------- Violations CRUD ---------- */

    public function test_violation_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Violations\ViolationForm::class)
            ->call('save')
            ->assertHasErrors(['inspection_id', 'violation_type']);

        Livewire::test(\App\Http\Livewire\Violations\ViolationForm::class)
            ->set('inspection_id', (string) $this->ids['inspection'])
            ->set('violation_type', 'TEST violation')
            ->set('severity', 'low')
            ->set('penalty_status', 'none')
            ->set('resolution_status', 'open')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('violations.index'));

        $id = \DB::table('violations')->where('violation_type', 'TEST violation')->value('violation_id');
        $this->assertNotNull($id);

        Livewire::test(\App\Http\Livewire\Violations\ViolationForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Violations\ViolationIndex::class)->call('delete', $id);
        $this->assertSoftDeleted('violations', ['violation_id' => $id]);
    }

    /* ---------- Incidents CRUD ---------- */

    public function test_incident_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Incidents\IncidentForm::class)
            ->call('save')
            ->assertHasErrors(['barangay_id', 'description']);

        Livewire::test(\App\Http\Livewire\Incidents\IncidentForm::class)
            ->set('barangay_id', (string) $this->ids['barangay'])
            ->set('incident_type', 'illegal_dumping')
            ->set('description', 'TEST incident description')
            ->set('date_reported', now()->format('Y-m-d'))
            ->set('status', 'reported')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('incidents.index'));

        $id = \DB::table('incidents')->where('description', 'TEST incident description')->value('incident_id');
        $this->assertNotNull($id);

        Livewire::test(\App\Http\Livewire\Incidents\IncidentForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Incidents\IncidentIndex::class)->call('delete', $id);
        $this->assertSoftDeleted('incidents', ['incident_id' => $id]);
    }

    /* ---------- Collections CRUD ---------- */

    public function test_collection_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Collections\CollectionForm::class)
            ->call('save')
            ->assertHasErrors(['barangay_id']);

        Livewire::test(\App\Http\Livewire\Collections\CollectionForm::class)
            ->set('barangay_id', (string) $this->ids['barangay'])
            ->set('collection_date', now()->addDay()->format('Y-m-d'))
            ->set('waste_type', 'mixed')
            ->set('status', 'pending')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('collections.index'));

        $id = \DB::table('collection_schedules')->orderByDesc('schedule_id')->value('schedule_id');
        Livewire::test(\App\Http\Livewire\Collections\CollectionForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Collections\CollectionIndex::class)->call('delete', $id);
        $this->assertDatabaseMissing('collection_schedules', ['schedule_id' => $id]);
    }

    /* ---------- Users CRUD ---------- */

    public function test_user_create_validate_delete(): void
    {
        Livewire::test(\App\Http\Livewire\Users\UserForm::class)
            ->call('save')
            ->assertHasErrors(['full_name', 'username', 'password', 'role_id']);

        Livewire::test(\App\Http\Livewire\Users\UserForm::class)
            ->set('full_name', 'TEST User')
            ->set('username', 'test_user_' . uniqid())
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role_id', (string) $this->ids['role'])
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index'));

        $id = \DB::table('users')->where('full_name', 'TEST User')->value('user_id');
        $this->assertNotNull($id);

        Livewire::test(\App\Http\Livewire\Users\UserForm::class, ['id' => $id])->assertOk();

        Livewire::test(\App\Http\Livewire\Users\UserIndex::class)->call('delete', $id);
    }
}
