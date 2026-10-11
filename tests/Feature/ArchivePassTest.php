<?php

namespace Tests\Feature;

use App\Http\Livewire\Archive\ArchiveIndex;
use App\Models\Inspection;
use App\Models\User;
use App\Models\ViolationTicket;
use App\Models\WasteEntry;
use App\Models\WasteGenerator;
use App\Support\Archivable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Deleting now archives: the row stays, hidden from its own list, and the
 * Archive is where it can be read, printed and put back.
 */
class ArchivePassTest extends TestCase
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

    private function newTicket(): ViolationTicket
    {
        return ViolationTicket::create([
            'ticket_number'  => '2099-' . random_int(1000, 9999),
            'violator_name'  => 'Archivo, Arturo ' . uniqid(),
            'violation_type' => 'littering',
            'address'        => 'Purok 9',
            'offense_number' => 1,
            'penalty_amount' => 0,
            'issued_date'    => '2026-02-02',
        ]);
    }

    // ── Access ──────────────────────────────────────────────────────────────

    public function test_the_archive_is_reachable_by_both_roles(): void
    {
        foreach (['admin', 'menro'] as $who) {
            $this->as($who);
            $this->get('/archive')->assertOk();
        }
    }

    public function test_user_management_and_the_audit_trail_stay_admin_only(): void
    {
        $this->as('menro');
        $this->get('/users')->assertForbidden();
        $this->get('/audit')->assertForbidden();
    }

    // ── Deleting archives rather than erases ────────────────────────────────

    public function test_every_archivable_type_soft_deletes(): void
    {
        foreach (Archivable::TYPES as $type => $config) {
            $model = new $config['model'];

            $this->assertTrue(
                in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true),
                "{$type} must soft delete so the archive can show it"
            );
            $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'deleted_at'),
                "{$model->getTable()} needs a deleted_at column");
        }
    }

    public function test_a_deleted_ticket_is_listed_in_the_archive(): void
    {
        $this->as('menro');
        $t = $this->newTicket();
        $t->delete();

        Livewire::test(ArchiveIndex::class)
            ->call('setTab', 'deleted')
            ->set('record_type', 'violation_tickets')
            ->assertSee($t->ticket_number);
    }

    // ── Restore ─────────────────────────────────────────────────────────────

    public function test_restoring_brings_the_record_back_and_is_audited(): void
    {
        $this->as('menro');
        $t = $this->newTicket();
        $t->delete();

        Livewire::test(ArchiveIndex::class)
            ->call('setTab', 'deleted')
            ->set('record_type', 'violation_tickets')
            ->call('restore', $t->ticket_id);

        $this->assertNull(ViolationTicket::withTrashed()->find($t->ticket_id)->deleted_at);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'restore',
            'module'    => 'ViolationTicket',
            'record_id' => $t->ticket_id,
        ]);
    }

    /**
     * The delete guards are not uniform - only an administrator may remove a
     * user - so restore must not be a blanket staff permission either.
     */
    public function test_staff_cannot_restore_a_type_they_could_not_have_deleted(): void
    {
        $this->as('menro');

        $this->assertFalse(Archivable::canRestore('users'));
        $this->assertFalse(Archivable::canRestore('barangays'));
        $this->assertTrue(Archivable::canRestore('violation_tickets'));

        $victim = User::where('username', 'viewer')->firstOrFail();
        $victim->delete();

        $status = null;
        try {
            $c = Livewire::test(ArchiveIndex::class)->set('record_type', 'users');
            DB::transaction(fn () => $c->call('restore', $victim->user_id));

            $e = $c->lastResponse->exception ?? null;
            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
            }
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
        }

        $this->assertSame(403, $status, 'MENRO Staff must not restore a user account');
        $this->assertNotNull(User::withTrashed()->find($victim->user_id)->deleted_at);
    }

    public function test_a_child_is_not_restored_while_its_parent_is_archived(): void
    {
        // As the administrator, so the ownership rule on entries (checked first,
        // as authorization should be) cannot be what refuses this.
        $this->as('admin');

        $generator = WasteGenerator::whereHas('wasteEntries')->firstOrFail();
        $entry     = $generator->wasteEntries()->firstOrFail();

        // Archiving the generator carries its entries along.
        $generator->delete();
        $this->assertNotNull(WasteEntry::withTrashed()->find($entry->entry_id)->deleted_at);

        // Called on the component directly rather than through Livewire's
        // harness, which keeps its own session and does not surface the flash.
        $component = new ArchiveIndex();
        $component->record_type = 'waste_entries';
        $component->restore($entry->entry_id);

        $this->assertNotNull(
            WasteEntry::withTrashed()->find($entry->entry_id)->deleted_at,
            'the entry must stay archived until its generator is back'
        );
        // The refusal has to name the parent, or the user has no way to know
        // what to do next.
        $this->assertSame('Restore the generator this record belongs to first.', session('error'));
    }

    /**
     * A waste entry belongs to whoever encoded it, so restoring one has to obey
     * the same ownership rule that deleting it does - not a flat role check.
     */
    public function test_a_staff_user_cannot_restore_another_persons_entry(): void
    {
        $mine   = User::where('username', 'encoder')->firstOrFail();
        $theirs = User::where('username', 'barangay')->firstOrFail();

        $entry = WasteEntry::whereNotNull('encoded_by')->firstOrFail();
        $entry->update(['encoded_by' => $theirs->user_id]);
        $entry->delete();

        $this->as('encoder');

        $status = null;
        try {
            $c = Livewire::test(ArchiveIndex::class)->set('record_type', 'waste_entries');
            DB::transaction(fn () => $c->call('restore', $entry->entry_id));

            $e = $c->lastResponse->exception ?? null;
            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
            }
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
        }

        $this->assertSame(403, $status, 'an entry encoded by someone else is not yours to restore');
        $this->assertNotNull(WasteEntry::withTrashed()->find($entry->entry_id)->deleted_at);

        // The administrator may correct any entry, here as everywhere else.
        $this->as('admin');
        $comp = new ArchiveIndex();
        $comp->record_type = 'waste_entries';
        $comp->restore($entry->entry_id);

        $this->assertNull(WasteEntry::withTrashed()->find($entry->entry_id)->deleted_at);
    }

    // ── The cascade ─────────────────────────────────────────────────────────

    public function test_archiving_a_generator_carries_its_children_and_restoring_returns_them(): void
    {
        $this->as('menro');

        $generator = WasteGenerator::whereHas('wasteEntries')->firstOrFail();
        $entryIds  = $generator->wasteEntries()->pluck('entry_id');
        $liveBefore = WasteEntry::count();

        $generator->delete();

        $this->assertSame($liveBefore - $entryIds->count(), WasteEntry::count(),
            'the generator\'s entries must leave the live totals with it');
        foreach ($entryIds as $id) {
            $this->assertNotNull(WasteEntry::withTrashed()->find($id)->deleted_at);
        }

        $generator->restore();

        $this->assertSame($liveBefore, WasteEntry::count(), 'restoring must bring them all back');
    }

    public function test_archiving_an_inspection_carries_its_violations(): void
    {
        $this->as('menro');

        $inspection = Inspection::whereHas('violations')->first();

        if (!$inspection) {
            $this->markTestSkipped('no inspection with violations in this dataset');
        }

        $ids = $inspection->violations()->pluck('violation_id');
        $inspection->delete();

        foreach ($ids as $id) {
            $this->assertNotNull(\App\Models\Violation::withTrashed()->find($id)->deleted_at);
        }
    }

    // ── Printing ────────────────────────────────────────────────────────────

    public function test_an_archived_ticket_can_still_be_printed_and_says_so(): void
    {
        $this->as('menro');
        $t = $this->newTicket();
        $t->delete();

        $this->get("/violation-tickets/{$t->ticket_id}/receipt")
            ->assertOk()
            ->assertSee($t->ticket_number)
            ->assertSee('Archived', false);
    }

    public function test_a_ticket_that_never_existed_is_still_a_404(): void
    {
        $this->as('menro');
        $this->get('/violation-tickets/99999999/receipt')->assertNotFound();
    }

    public function test_the_archive_list_prints(): void
    {
        $this->as('menro');
        $t = $this->newTicket();
        $t->delete();

        $this->get('/archive/print?type=violation_tickets')
            ->assertOk()
            ->assertSee('Archived Violation Tickets')
            ->assertSee($t->violator_name);
    }

    public function test_printing_an_unknown_record_type_is_a_404(): void
    {
        $this->as('menro');
        $this->get('/archive/print?type=not_a_real_type')->assertNotFound();
    }

    /**
     * Raw SQL bypasses the soft-delete global scope entirely, so an archived row
     * silently reappears in any aggregate written that way. This is mechanical
     * and a little ugly, and it is the only thing that will stop the next raw
     * query from reintroducing the bug.
     */
    public function test_raw_sql_over_soft_deleting_tables_filters_deleted_rows(): void
    {
        $tables = 'waste_entries|waste_generators|barangays|collection_schedules|users|clusters'
                . '|sector_members|inspections|violations|incidents|violation_tickets';

        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());

            if (!preg_match('/(?:FROM|JOIN)\s+(?:' . $tables . ')/i', $code)) {
                continue;
            }

            // Every file that reaches a soft-deleting table in raw SQL has to
            // say deleted_at somewhere. Coarse, but it fails loudly on the one
            // mistake that would otherwise go unnoticed for months.
            if (!str_contains($code, 'deleted_at')) {
                $offenders[] = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders,
            "raw SQL over a soft-deleting table with no deleted_at filter:
" . implode("
", $offenders));
    }

    // ── Attribution survives archival ───────────────────────────────────────

    public function test_an_archived_users_name_still_prints_on_their_ticket(): void
    {
        $this->as('menro');

        $issuer = User::where('username', 'inspector')->firstOrFail();
        $t = $this->newTicket();
        $t->update(['issued_by' => $issuer->user_id]);

        $issuer->delete();

        $this->get("/violation-tickets/{$t->ticket_id}/receipt")
            ->assertOk()
            ->assertSee($issuer->full_name);
    }

    public function test_an_archived_user_cannot_sign_in(): void
    {
        $u = User::where('username', 'viewer')->firstOrFail();
        $u->delete();

        $this->flushSession();
        $this->post('/login', ['username' => 'viewer', 'password' => 'admin123'])
            ->assertRedirect();

        $this->assertNull(session('auth_user_id'), 'an archived account must not get a session');
    }

    public function test_an_archived_users_live_session_ends_on_the_next_request(): void
    {
        $u = $this->as('viewer');
        $this->get('/dashboard')->assertOk();

        $u->delete();

        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
