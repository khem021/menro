<?php

namespace App\Http\Livewire\ViolationTickets;

use App\Models\ViolationTicket;
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
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $t = ViolationTicket::findOrFail($id);
        logAudit('delete', 'ViolationTicket', $id, $t->toArray());
        $t->delete();
        Cache::forget('stats:violation_tickets');
        session()->flash('success', 'Violation ticket deleted.');
    }

    public function render()
    {
        $tickets = ViolationTicket::with('issuedBy:user_id,full_name')
            ->when($this->search, fn($q) =>
                $q->where(function ($q2) {
                    $q2->where('violator_name', 'ILIKE', '%' . $this->search . '%')
                       ->orWhere('ticket_number', 'ILIKE', '%' . $this->search . '%');
                })
            )
            ->when($this->violation_type, fn($q) => $q->where('violation_type', $this->violation_type))
            ->orderByDesc('issued_date')
            ->orderByDesc('ticket_id')
            ->paginate($this->perPage);

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

        return view('livewire.violation-tickets.violation-ticket-index', compact('tickets', 'stats'))
            ->extends('layouts.app');
    }
}
