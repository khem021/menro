<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Moves every ticket still on the old RA 9003 placeholder schedule onto the
 * municipal one: a first offense is a reprimand carrying no fine, a second is
 * 500 and a third — and every later offense — is 1,000.
 */
return new class extends Migration
{
    /** Mirrors ViolationTicket::FINE_TIERS as of this migration. */
    private const NEW_TIERS = [1 => 0.00, 2 => 500.00, 3 => 1000.00];

    /** The schedule this replaces, kept so down() has somewhere to go back to. */
    private const OLD_TIERS = [1 => 500.00, 2 => 1000.00, 3 => 2500.00];

    public function up(): void
    {
        $this->retier(self::OLD_TIERS, self::NEW_TIERS);
    }

    /**
     * Reverses up() for every row it actually moved. Three things it cannot
     * undo, which is why the pre-migration dump is the real revert:
     *
     *   - A row that already sat on a target amount for an unrelated reason is
     *     moved by down() even though up() left it alone.
     *   - Stored offense numbers are not re-clamped. A 5th-offense row goes back
     *     to 2,500 but stays offense_number = 5; harmless, since the old
     *     fineForOffense() clamped at 3 anyway.
     *   - It does not recompute offense_number for repeat offenders. An issued
     *     ticket is a legal document that was correct under the then-current
     *     rule, and tiers 3 and up cost the same either way.
     */
    public function down(): void
    {
        $this->retier(self::NEW_TIERS, self::OLD_TIERS);
    }

    /**
     * Repoint every ticket sitting on tier N of $from onto tier N of $to.
     *
     * Two deliberate properties:
     *
     *   - Only rows that are ON schedule move. A penalty matching no $from
     *     amount was set by hand or predates a tier change, and a figure on a
     *     ticket already served to someone must not change underneath them.
     *
     *   - Each pass filters on offense_number as well as amount, which is what
     *     keeps the passes from overlapping. That matters here because the old
     *     tier-1 amount (500) is the new tier-2 amount and the old tier-2 amount
     *     (1000) is the new tier-3 amount: without the offense_number predicate
     *     a row updated by one pass would be caught again by the next.
     *
     * The query builder sees no Eloquent global scope, so archived tickets are
     * updated too. They have to be — restoring one must not reintroduce the old
     * figure.
     */
    private function retier(array $from, array $to): void
    {
        DB::transaction(function () use ($from, $to) {
            foreach ($from as $tier => $oldAmount) {
                $query = DB::table('violation_tickets')->where('penalty_amount', $oldAmount);

                // Tier 3 is the cap, so it also owns offense_number 4 and up.
                $tier === 3
                    ? $query->where('offense_number', '>=', 3)
                    : $query->where('offense_number', $tier);

                $query->update(['penalty_amount' => $to[$tier], 'updated_at' => now()]);
            }
        });

        // "Total Penalties Assessed" is a cached SUM(penalty_amount), and the
        // update above is a query-builder write, so no model event fired and
        // InvalidatesDataCache never ran.
        try {
            Cache::forget('stats:violation_tickets');
        } catch (\Throwable $e) {
            // The cache store can be unreachable mid-deploy; `cache:clear` in
            // the deploy script is the backstop.
        }
    }
};
