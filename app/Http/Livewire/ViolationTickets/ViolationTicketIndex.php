<?php

namespace App\Http\Livewire\ViolationTickets;

use App\Models\ViolationTicket;
use App\Support\Like;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ViolationTicketIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search         = '';
    public string $violation_type = '';
    public int    $perPage        = 15;

    public function updatingSearch()        { $this->resetPage(); }
    public function updatingViolationType() { $this->resetPage(); }

    public function delete($id)
    {
        if (!canAccess(...ROLES_STAFF())) {
            abort(403, 'Access denied.');
        }

        $t = ViolationTicket::findOrFail($id);
        logAudit('delete', 'ViolationTicket', $id, $t->toArray());
        $t->delete();
        session()->flash('success', 'Violation ticket deleted.');
    }

    /**
     * The one definition of "which tickets are in scope". Used by both the list
     * and the Total Penalties card, so the card can never disagree with the
     * rows on screen.
     */
    private function ticketQuery()
    {
        return ViolationTicket::query()
            ->when($this->search, fn($q) =>
                $q->where(function ($q2) {
                    $q2->whereRaw('LOWER(violator_name) LIKE ?', [Like::contains($this->search)])
                       ->orWhereRaw('LOWER(ticket_number) LIKE ?', [Like::contains($this->search)]);
                })
            )
            ->when($this->violation_type, fn($q) => $q->where('violation_type', $this->violation_type));
    }

    /** True when the list is showing a subset rather than every ticket. */
    private function isFiltered(): bool
    {
        return trim($this->search) !== '' || $this->violation_type !== '';
    }

    /**
     * Says what the narrowed Total Penalties figure covers.
     *
     * Deliberately phrased as "matching" rather than as a person's total: the
     * search is a substring match (Like::contains) while the offense counter
     * matches the violator name exactly, so two spellings of one name are one
     * search result but two different violators as far as the offense tier is
     * concerned. The card must promise only what it delivers.
     */
    private function scopeLabel(int $matching): string
    {
        $parts = [];

        if (trim($this->search) !== '') {
            $parts[] = 'Matching "' . trim($this->search) . '"';
        }

        if ($this->violation_type !== '') {
            $parts[] = ViolationTicket::VIOLATION_TYPES[$this->violation_type] ?? $this->violation_type;
        }

        return implode(' · ', $parts) . ' · ' . $matching . ' ticket' . ($matching === 1 ? '' : 's');
    }

    public function render()
    {
        $tickets = $this->ticketQuery()
            ->with('issuedBy:user_id,full_name')
            ->orderByDesc('issued_date')
            ->orderByDesc('ticket_id')
            ->paginate($this->perPage);

        // Three of the four cards are municipality-wide, so they stay on the one
        // cached key DataCache knows how to invalidate.
        $stats = Cache::remember('stats:violation_tickets', 60, function () {
            $monthStart = now()->startOfMonth()->toDateString();
            $today      = today()->toDateString();
            $row = ViolationTicket::selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN issued_date = ? THEN 1 ELSE 0 END) AS today_count,
                SUM(CASE WHEN issued_date >= ? THEN 1 ELSE 0 END) AS month_count,
                COALESCE(SUM(penalty_amount), 0) AS total_penalties
            ", [$today, $monthStart])->first();
            return [
                'total'           => (int) $row->total,
                'today_count'     => (int) $row->today_count,
                'month_count'     => (int) $row->month_count,
                'total_penalties' => (float) $row->total_penalties,
            ];
        });

        $stats['penalties_scope'] = null;

        if ($this->isFiltered()) {
            // Deliberately not cached. One key per search term would grow
            // without bound - every keystroke past the 300ms debounce is a
            // distinct term - and DataCache cannot invalidate keys it does not
            // know about. This is one indexed SUM over a small table.
            //
            // Cache::remember returned a value and PHP arrays are value types,
            // so overwriting it here does not write back to the store.
            $stats['total_penalties'] = (float) $this->ticketQuery()->sum('penalty_amount');
            $stats['penalties_scope'] = $this->scopeLabel($tickets->total());
        }

        return view('livewire.violation-tickets.violation-ticket-index', compact('tickets', 'stats'))
            ->extends('layouts.app');
    }
}
