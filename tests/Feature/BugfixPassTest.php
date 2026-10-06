<?php

namespace Tests\Feature;

use App\Http\Livewire\Entries\EntryForm;
use App\Http\Livewire\Entries\EntryIndex;
use App\Http\Livewire\Generators\GeneratorIndex;
use App\Http\Livewire\Inspections\InspectionForm;
use App\Http\Livewire\Inspections\InspectionIndex;
use App\Http\Livewire\ViolationTickets\ViolationTicketForm;
use App\Models\Inspection;
use App\Models\User;
use App\Models\ViolationTicket;
use App\Models\WasteEntry;
use App\Models\WasteGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Regression tests for the Phase 2 audit (docs/BUG_LIST.md). Each test name
 * starts with the bug id it covers. Writes run in a rolled-back transaction.
 */
class BugfixPassTest extends TestCase
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

    /** Runs a Livewire action and returns the HTTP status it aborted with, or null if it ran. */
    private function actionStatus(string $class, string $method, array $params = [], array $props = []): ?int
    {
        try {
            $c = Livewire::test($class);
            foreach ($props as $k => $v) {
                $c->set($k, $v);
            }
            DB::transaction(fn () => $c->call($method, ...$params));

            // Livewire 2's test harness keeps an abort() on the response
            // instead of throwing it.
            $e = $c->lastResponse->exception ?? null;
            if ($e instanceof HttpException) {
                return $e->getStatusCode();
            }
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }

        return null;
    }

    private function newGenerator(): WasteGenerator
    {
        $src = WasteGenerator::firstOrFail();

        return WasteGenerator::create([
            'generator_name' => 'Probe Generator ' . uniqid(),
            'generator_type_id' => $src->generator_type_id,
            'barangay_id' => $src->barangay_id,
            'compliance_status' => 'compliant',
            'status' => 'active',
        ]);
    }

    private function newEntry(?int $encodedBy): WasteEntry
    {
        $g = WasteGenerator::firstOrFail();

        return WasteEntry::create([
            'generator_id' => $g->generator_id,
            'category_id' => DB::table('waste_categories')->min('category_id'),
            'quantity' => 5, 'unit' => 'kg', 'entry_date' => '2026-10-01',
            'encoded_by' => $encodedBy,
        ]);
    }

    /* ---------- B1: delete authorization ---------- */

    /** @dataProvider nonManagers */
    public function test_b1_generators_cannot_be_deleted_by(string $who): void
    {
        $g = $this->newGenerator();
        $this->as($who);

        $this->assertSame(403, $this->actionStatus(GeneratorIndex::class, 'delete', [$g->generator_id]));
        $this->assertTrue(WasteGenerator::whereKey($g->generator_id)->exists());
    }

    public static function nonManagers(): array
    {
        return ['encoder' => ['encoder'], 'inspector' => ['inspector'], 'barangay' => ['barangay'], 'viewer' => ['viewer']];
    }

    public function test_b1_officer_and_admin_can_delete_a_generator(): void
    {
        foreach (['menro', 'admin'] as $who) {
            $g = $this->newGenerator();
            $this->as($who);
            $this->assertNull($this->actionStatus(GeneratorIndex::class, 'delete', [$g->generator_id]));
            $this->assertFalse(WasteGenerator::whereKey($g->generator_id)->exists());
        }
    }

    public function test_b1_an_entry_can_only_be_deleted_by_its_encoder_or_an_admin(): void
    {
        $encoder = User::where('username', 'encoder')->firstOrFail();
        $mine = $this->newEntry($encoder->user_id);
        $theirs = $this->newEntry(User::where('username', 'barangay')->value('user_id'));

        $this->as('encoder');
        $this->assertSame(403, $this->actionStatus(EntryIndex::class, 'delete', [$theirs->entry_id]));
        $this->assertTrue(WasteEntry::whereKey($theirs->entry_id)->exists());
        $this->assertNull($this->actionStatus(EntryIndex::class, 'delete', [$mine->entry_id]));
        $this->assertFalse(WasteEntry::whereKey($mine->entry_id)->exists());

        $this->as('admin');
        $this->assertNull($this->actionStatus(EntryIndex::class, 'delete', [$theirs->entry_id]));
    }

    /** @dataProvider nonManagers */
    public function test_b1_inspections_cannot_be_deleted_by(string $who): void
    {
        $id = Inspection::firstOrFail()->inspection_id;
        $this->as($who);

        $this->assertSame(403, $this->actionStatus(InspectionIndex::class, 'delete', [$id]));
        $this->assertTrue(Inspection::whereKey($id)->exists());
    }

    /* ---------- B2: confirmations must be real on Livewire 2 ---------- */

    public function test_b2_no_view_uses_the_livewire_3_only_wire_confirm(): void
    {
        $offenders = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $f) {
            if ($f->isFile() && str_contains(file_get_contents($f->getPathname()), 'wire:confirm')) {
                $offenders[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $f->getPathname());
            }
        }
        $this->assertSame([], $offenders, 'wire:confirm is inert on Livewire 2.');
    }

    public function test_b2_delete_buttons_ask_by_name_before_acting(): void
    {
        $this->as('admin');
        $name = WasteGenerator::orderBy('generator_name')->value('generator_name');

        $this->get('/generators')->assertOk()->assertSee('confirm(', false)->assertSee('Delete generator', false);
        $this->assertNotEmpty($name);
    }

    /* ---------- B3: offense count ---------- */

    private function issueTicket(string $name): ViolationTicket
    {
        Livewire::test(ViolationTicketForm::class)
            ->set('violator_name', $name)->set('violation_type', 'littering')
            ->set('address', 'Somewhere')->set('issued_date', '2026-10-05')->call('save');

        return ViolationTicket::latest('ticket_id')->firstOrFail();
    }

    public function test_b3_wildcards_in_a_name_do_not_inflate_the_offense(): void
    {
        $this->as('admin');
        foreach (['Juan Dela Cruz', 'Maria Santos'] as $n) {
            ViolationTicket::create(['ticket_number' => '2097-' . random_int(1000, 9999), 'violator_name' => $n,
                'violation_type' => 'littering', 'address' => 'x', 'offense_number' => 1, 'penalty_amount' => 500, 'issued_date' => '2026-01-01']);
        }

        $this->assertSame(1, $this->issueTicket('%')->offense_number);
        $this->assertSame(1, $this->issueTicket('J_an Dela Cruz')->offense_number);
        $this->assertSame(1, $this->issueTicket('Brand New Person')->offense_number);
    }

    public function test_b3_the_same_name_in_any_case_is_still_a_repeat_offense(): void
    {
        $this->as('admin');
        ViolationTicket::create(['ticket_number' => '2097-' . random_int(1000, 9999), 'violator_name' => 'Juan Dela Cruz',
            'violation_type' => 'littering', 'address' => 'x', 'offense_number' => 1, 'penalty_amount' => 500, 'issued_date' => '2026-01-01']);

        $t = $this->issueTicket('  juan DELA cruz ');
        $this->assertSame(2, $t->offense_number);
        $this->assertEquals(1000.00, (float) $t->penalty_amount);
    }

    /* ---------- B4: queries must not be PostgreSQL-only ---------- */

    public function test_b4_no_postgres_only_sql_in_the_app(): void
    {
        $offenders = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $f) {
            if ($f->isFile() && preg_match('/\b(ILIKE|SPLIT_PART)\b/', file_get_contents($f->getPathname()))) {
                $offenders[] = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $f->getPathname());
            }
        }
        $this->assertSame([], $offenders);
    }

    public function test_b4_ticket_numbers_keep_counting_past_9999_and_across_years(): void
    {
        foreach (['2096-0007', '2096-9999', '2096-10000', '2095-0003'] as $n) {
            ViolationTicket::create(['ticket_number' => $n, 'violator_name' => 'N' . $n, 'violation_type' => 'littering',
                'address' => 'x', 'offense_number' => 1, 'penalty_amount' => 500, 'issued_date' => '2026-01-01']);
        }
        $this->assertSame('2096-10001', ViolationTicket::nextTicketNumber('2096'));
        $this->assertSame('2095-0004', ViolationTicket::nextTicketNumber('2095'));
        $this->assertSame('2094-0001', ViolationTicket::nextTicketNumber('2094'));
    }

    /* ---------- B5: who may open ticket / inspection lists ---------- */

    /** @dataProvider listAccess */
    public function test_b5_list_pages_follow_the_module_roles(string $path, array $allowed): void
    {
        $t = ViolationTicket::create(['ticket_number' => '2093-0001', 'violator_name' => 'Pat', 'violation_type' => 'littering',
            'address' => 'x', 'offense_number' => 1, 'penalty_amount' => 500, 'issued_date' => '2026-01-01']);
        $path = str_replace('{ticket}', (string) $t->ticket_id, $path);

        foreach (['admin', 'menro', 'encoder', 'inspector', 'barangay', 'viewer'] as $who) {
            $this->as($who);
            $status = $this->get($path)->getStatusCode();
            $this->assertSame(in_array($who, $allowed, true) ? 200 : 403, $status, "$who on $path");
        }
    }

    public static function listAccess(): array
    {
        $three = ['admin', 'menro', 'inspector'];

        return [
            'tickets' => ['/violation-tickets', $three],
            'receipt' => ['/violation-tickets/{ticket}/receipt', $three],
            'violations' => ['/violations', $three],
            'inspections' => ['/inspections', $three],
            'compliance' => ['/compliance', $three],
            'collections' => ['/collections', ['admin', 'menro']],
        ];
    }

    public function test_b5_a_missing_ticket_receipt_is_a_404_for_allowed_roles(): void
    {
        $this->as('admin');
        $this->get('/violation-tickets/99999999/receipt')->assertNotFound();
    }

    /* ---------- B6: signed-in checks must also cover Livewire requests ---------- */

    public function test_b6_livewire_requests_pass_through_the_session_check(): void
    {
        $this->flushSession();
        $this->postJson('/livewire/message/generators.generator-index', [], ['X-Livewire' => 'true'])->assertStatus(419);
    }

    public function test_b6_a_deactivated_user_is_refused_on_livewire_requests(): void
    {
        $u = $this->as('barangay');
        $u->update(['status' => 'inactive']);

        $this->postJson('/livewire/message/generators.generator-index', [], ['X-Livewire' => 'true'])->assertStatus(419);
    }

    /* ---------- B7 / B14: bounds on numbers and dates ---------- */

    private function saveEntry(string $qty, string $date = '2026-10-05'): array
    {
        $c = Livewire::test(EntryForm::class)
            ->set('generator_id', (string) WasteGenerator::value('generator_id'))
            ->set('category_id', (string) DB::table('waste_categories')->min('category_id'))
            ->set('quantity', $qty)->set('entry_date', $date);
        DB::transaction(fn () => $c->call('save'));

        return array_keys($c->payload['serverMemo']['errors'] ?? []);
    }

    public function test_b7_an_oversized_quantity_is_a_validation_error_not_a_crash(): void
    {
        $this->as('admin');
        $this->assertSame(['quantity'], $this->saveEntry('100000000'));
        $this->assertSame([], $this->saveEntry('99999999.99'));
    }

    public function test_b14_absurd_entry_dates_are_rejected(): void
    {
        $this->as('admin');
        $this->assertSame(['entry_date'], $this->saveEntry('5', '0001-01-01'));
        $this->assertSame(['entry_date'], $this->saveEntry('5', '9999-12-31'));
        $this->assertSame([], $this->saveEntry('5', now()->toDateString()));
    }

    public function test_b14_a_follow_up_cannot_be_before_the_inspection(): void
    {
        $this->as('admin');
        $c = Livewire::test(InspectionForm::class)
            ->set('generator_id', (string) WasteGenerator::value('generator_id'))
            ->set('inspection_date', '2026-10-05')
            ->set('inspector_id', (string) User::where('username', 'inspector')->value('user_id'))
            ->set('compliance_status', 'compliant')->set('segregation_score', '80')
            ->set('next_follow_up', '2020-01-01');
        DB::transaction(fn () => $c->call('save'));

        $this->assertSame(['next_follow_up'], array_keys($c->payload['serverMemo']['errors'] ?? []));
    }

    /* ---------- B8: a blank score stays blank ---------- */

    public function test_b8_a_blank_segregation_score_is_stored_as_null(): void
    {
        $this->as('admin');
        $before = Inspection::max('inspection_id');
        $c = Livewire::test(InspectionForm::class)
            ->set('generator_id', (string) WasteGenerator::value('generator_id'))
            ->set('inspection_date', '2026-10-05')
            ->set('inspector_id', (string) User::where('username', 'inspector')->value('user_id'))
            ->set('compliance_status', 'compliant')->set('segregation_score', '');
        DB::transaction(fn () => $c->call('save'));

        $row = Inspection::where('inspection_id', '>', $before)->latest('inspection_id')->firstOrFail();
        $this->assertNull($row->segregation_score);
    }

    /* ---------- B9: only the latest inspection drives the generator's status ---------- */

    public function test_b9_editing_an_old_inspection_leaves_the_generator_status_alone(): void
    {
        $this->as('admin');
        $g = $this->newGenerator();
        $g->update(['compliance_status' => 'non_compliant']);
        $inspector = User::where('username', 'inspector')->value('user_id');
        $old = Inspection::create(['generator_id' => $g->generator_id, 'inspection_date' => '2025-01-01', 'inspector_id' => $inspector,
            'compliance_status' => 'violation', 'segregation_score' => 10]);
        Inspection::create(['generator_id' => $g->generator_id, 'inspection_date' => '2026-01-01', 'inspector_id' => $inspector,
            'compliance_status' => 'violation', 'segregation_score' => 10]);

        DB::transaction(fn () => Livewire::test(InspectionForm::class, ['id' => $old->inspection_id])
            ->set('compliance_status', 'compliant')->call('save'));

        $this->assertSame('non_compliant', $g->fresh()->compliance_status);
    }

    public function test_b9_editing_the_latest_inspection_still_updates_the_generator(): void
    {
        $this->as('admin');
        $g = $this->newGenerator();
        $inspector = User::where('username', 'inspector')->value('user_id');
        $latest = Inspection::create(['generator_id' => $g->generator_id, 'inspection_date' => '2026-01-01', 'inspector_id' => $inspector,
            'compliance_status' => 'violation', 'segregation_score' => 10]);

        DB::transaction(fn () => Livewire::test(InspectionForm::class, ['id' => $latest->inspection_id])
            ->set('compliance_status', 'compliant')->call('save'));

        $this->assertSame('compliant', $g->fresh()->compliance_status);
    }

    /* ---------- B13: search wildcards are literal ---------- */

    public function test_b13_a_percent_in_search_does_not_match_everything(): void
    {
        $this->as('admin');
        $all = Livewire::test(GeneratorIndex::class)->lastRenderedDom;
        $pct = Livewire::test(GeneratorIndex::class)->set('search', '%')->lastRenderedDom;

        $this->assertGreaterThan(0, substr_count($all, 'btn-icon-del'));
        $this->assertSame(0, substr_count($pct, 'btn-icon-del'));
    }

    /* ---------- B15: opening someone else's entry ---------- */

    public function test_b15_an_entry_edit_form_is_refused_to_other_users(): void
    {
        $theirs = $this->newEntry(User::where('username', 'barangay')->value('user_id'));
        $mine = $this->newEntry(User::where('username', 'encoder')->value('user_id'));

        $this->as('encoder');
        $this->get("/entries/{$theirs->entry_id}/edit")->assertForbidden();
        $this->get("/entries/{$mine->entry_id}/edit")->assertOk();

        $this->as('admin');
        $this->get("/entries/{$theirs->entry_id}/edit")->assertOk();
    }

    /* ---------- B16: lockout wording ---------- */

    public function test_b16_the_fifth_failure_says_the_account_is_now_locked(): void
    {
        $this->flushSession();
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $this->assertStringContainsString('temporarily locked', implode(' ', session('errors')->get('login')));
    }

    /* ---------- B18: login page accessibility ---------- */

    public function test_b18_the_password_toggle_is_labelled_and_the_page_resets_on_back(): void
    {
        $this->flushSession();
        $this->get('/login')->assertOk()
            ->assertSee('aria-label="Show password"', false)
            ->assertSee('pageshow', false);
    }
}
