<?php

namespace Tests\Feature;

use App\Http\Livewire\ViolationTickets\ViolationTicketForm;
use App\Http\Livewire\ViolationTickets\ViolationTicketIndex;
use App\Models\User;
use App\Models\ViolationTicket;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Violation module after the October 2026 pass: the municipal penalty
 * schedule, manual ticket numbers, the column order and the per-search
 * penalties total.
 */
class ViolationTicketPassTest extends TestCase
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

    private function issue(array $overrides = []): \Livewire\Testing\TestableLivewire
    {
        $c = Livewire::test(ViolationTicketForm::class)
            ->set('violator_name', $overrides['violator_name'] ?? 'Dela Cruz, Juan')
            ->set('address', $overrides['address'] ?? 'Purok 1')
            ->set('violation_type', $overrides['violation_type'] ?? 'littering')
            ->set('issued_date', $overrides['issued_date'] ?? '2026-03-04');

        foreach ($overrides as $k => $v) {
            if (!in_array($k, ['violator_name', 'address', 'violation_type', 'issued_date'], true)) {
                $c->set($k, $v);
            }
        }

        return $c->call('save');
    }

    // ── The penalty schedule ────────────────────────────────────────────────

    /** @dataProvider scheduleProvider */
    public function test_the_penalty_schedule(int $offense, string $label, float $fine): void
    {
        $this->assertSame($label, ViolationTicket::offenseLabel($offense));
        $this->assertSame($fine, ViolationTicket::fineForOffense($offense));
    }

    public static function scheduleProvider(): array
    {
        return [
            '1st is a reprimand with no fine' => [1, 'Reprimand', 0.00],
            '2nd'                             => [2, '2nd Offense', 500.00],
            '3rd'                             => [3, '3rd Offense', 1000.00],
            // The money caps at the third tier; the count does not.
            '4th still costs the 3rd fine'    => [4, '4th Offense', 1000.00],
            '7th'                             => [7, '7th Offense', 1000.00],
            '11th is not 11st'                => [11, '11th Offense', 1000.00],
            '21st is not 21th'                => [21, '21st Offense', 1000.00],
            '99th'                            => [99, '99th Offense', 1000.00],
        ];
    }

    public function test_repeat_offenses_escalate_then_cap(): void
    {
        $this->as('menro');
        $name = 'Escalante, Teodoro ' . uniqid();

        foreach ([[1, 0.00], [2, 500.00], [3, 1000.00], [4, 1000.00]] as [$offense, $fine]) {
            $this->issue(['violator_name' => $name]);

            $t = ViolationTicket::where('violator_name', $name)->orderByDesc('ticket_id')->first();

            $this->assertSame($offense, $t->offense_number, "ticket {$offense} offense number");
            $this->assertSame($fine, (float) $t->penalty_amount, "ticket {$offense} penalty");
        }
    }

    // ── Ticket numbers, manual and automatic ────────────────────────────────

    public function test_an_auto_number_is_assigned_and_well_formed(): void
    {
        $this->as('menro');
        $this->issue(['violator_name' => 'Auto, Ana ' . uniqid()]);

        $t = ViolationTicket::orderByDesc('ticket_id')->first();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{4,}$/', $t->ticket_number);
        $this->assertStringStartsWith('2026-', $t->ticket_number);
    }

    public function test_the_create_form_previews_the_next_number(): void
    {
        $this->as('menro');

        Livewire::test(ViolationTicketForm::class)
            ->assertSet('auto_ticket_number', true)
            ->assertSee(ViolationTicket::nextTicketNumber(now()->format('Y')));
    }

    public function test_a_manual_number_is_stored_verbatim(): void
    {
        $this->as('menro');
        $this->issue([
            'violator_name'      => 'Manual, Marta ' . uniqid(),
            'auto_ticket_number' => false,
            'ticket_number'      => '2030-0042',
        ]);

        $this->assertDatabaseHas('violation_tickets', ['ticket_number' => '2030-0042']);
    }

    public function test_a_manual_number_clashing_with_a_live_one_is_refused(): void
    {
        $this->as('menro');

        // The table can be empty on a freshly seeded database, so issue the
        // ticket whose number is then clashed with.
        $this->issue(['violator_name' => 'Taken, Tomas ' . uniqid()]);
        $taken = ViolationTicket::orderByDesc('ticket_id')->value('ticket_number');

        $this->issue([
            'violator_name'      => 'Clash, Carlo',
            'auto_ticket_number' => false,
            'ticket_number'      => $taken,
        ])->assertHasErrors('ticket_number');

        $this->assertSame(1, ViolationTicket::withTrashed()->where('ticket_number', $taken)->count());
    }

    /**
     * nextTicketNumber() uses withTrashed() so a number is never reissued; the
     * manual path has to agree, or an archived ticket and a new one could share
     * a number.
     */
    public function test_a_manual_number_clashing_with_an_archived_one_is_refused(): void
    {
        $this->as('menro');
        $archived = ViolationTicket::onlyTrashed()->value('ticket_number');

        if (!$archived) {
            $this->issue(['violator_name' => 'ToArchive, Tina']);
            $t = ViolationTicket::orderByDesc('ticket_id')->first();
            $t->delete();
            $archived = $t->ticket_number;
        }

        $this->issue([
            'violator_name'      => 'Clash, Cora',
            'auto_ticket_number' => false,
            'ticket_number'      => $archived,
        ])->assertHasErrors('ticket_number');
    }

    /** @dataProvider malformedNumbers */
    public function test_a_malformed_manual_number_is_refused(string $number): void
    {
        $this->as('menro');

        $this->issue([
            'violator_name'      => 'Bad, Boris',
            'auto_ticket_number' => false,
            'ticket_number'      => $number,
        ])->assertHasErrors('ticket_number');
    }

    public static function malformedNumbers(): array
    {
        // Free text inside the year prefix would break nextTicketNumber()'s
        // (int) substr() parse and make the auto sequence reissue numbers.
        return [
            'not a number'   => ['banana'],
            'letters in seq' => ['2026-ab'],
            'too short'      => ['2026-1'],
            'no year'        => ['0042'],
        ];
    }

    // ── The table column order ──────────────────────────────────────────────

    public function test_address_is_listed_before_type_of_violation(): void
    {
        $this->as('menro');
        $html = Livewire::test(ViolationTicketIndex::class)->payload['effects']['html'];

        $this->assertLessThan(
            strpos($html, '<th class="table-header">Type of Violation</th>'),
            strpos($html, '<th class="table-header">Address</th>'),
            'Address must come before Type of Violation'
        );
    }

    // ── The per-search penalties total ──────────────────────────────────────

    public function test_total_penalties_narrows_to_the_search(): void
    {
        $this->as('menro');

        $a = 'Tandag, Ambrosio ' . uniqid();
        $b = 'Bislig, Benedicta ' . uniqid();

        $this->issue(['violator_name' => $a]);                       // reprimand, 0
        $this->issue(['violator_name' => $a]);                       // 2nd, 500
        $this->issue(['violator_name' => $b]);                       // reprimand, 0

        $c = Livewire::test(ViolationTicketIndex::class);

        // Unfiltered: the municipality-wide figure, and no scope subtitle.
        $all = $c->viewData('stats');
        $this->assertNull($all['penalties_scope']);

        $c->set('search', $a);
        $this->assertSame(500.00, $c->viewData('stats')['total_penalties']);
        $this->assertStringContainsString('2 tickets', $c->viewData('stats')['penalties_scope']);

        $c->set('search', $b);
        $this->assertSame(0.00, $c->viewData('stats')['total_penalties']);

        $c->set('search', 'zzz-matches-nothing');
        $this->assertSame(0.00, $c->viewData('stats')['total_penalties']);
    }

    public function test_the_filtered_total_respects_the_type_filter(): void
    {
        $this->as('menro');
        $name = 'Mixed, Mario ' . uniqid();

        $this->issue(['violator_name' => $name, 'violation_type' => 'littering']);
        $this->issue(['violator_name' => $name, 'violation_type' => 'burning']);   // 2nd, 500

        $c = Livewire::test(ViolationTicketIndex::class)->set('search', $name);
        $this->assertSame(500.00, $c->viewData('stats')['total_penalties']);

        $c->set('violation_type', 'littering');
        $this->assertSame(0.00, $c->viewData('stats')['total_penalties'], 'the reprimand only');
    }

    /**
     * The filtered figure must not be written to the shared cache key: one entry
     * per search term would grow without bound and DataCache has no way to
     * invalidate keys it does not know about.
     */
    public function test_a_filtered_render_does_not_touch_the_shared_stats_cache(): void
    {
        $this->as('menro');

        Cache::forget('stats:violation_tickets');
        Livewire::test(ViolationTicketIndex::class);
        $unfiltered = Cache::get('stats:violation_tickets');

        $this->assertNotNull($unfiltered, 'an unfiltered render populates the key');

        Livewire::test(ViolationTicketIndex::class)->set('search', 'jamora');

        $this->assertEquals($unfiltered, Cache::get('stats:violation_tickets'),
            'a filtered render must leave the shared key untouched');
    }
}
