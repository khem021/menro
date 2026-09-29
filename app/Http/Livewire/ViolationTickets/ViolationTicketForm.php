<?php

namespace App\Http\Livewire\ViolationTickets;

use App\Models\ViolationTicket;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ViolationTicketForm extends Component
{
    public ?int $ticketId = null;

    public string $violator_name              = '';
    public string $violation_type             = '';
    public string $other_violation_description = '';
    public string $address                    = '';
    public string $issued_date                = '';
    public string $remarks                    = '';

    // Read-only, display-only — populated in mount() when editing.
    public ?string $ticket_number  = null;
    public ?int    $offense_number = null;
    public ?float  $penalty_amount = null;

    protected $rules = [
        'violator_name'               => 'required|string|max:255',
        'violation_type'              => 'required|in:littering,dumping,burning,no_segregation,other',
        'other_violation_description' => 'required_if:violation_type,other|nullable|string|max:255',
        'address'                     => 'required|string|max:255',
        'issued_date'                 => 'required|date',
        'remarks'                     => 'nullable|string|max:1000',
    ];

    public function mount($id = null)
    {
        if (!canAccess('System Administrator', 'MENRO Officer', 'Field Inspector')) {
            abort(403, 'Access denied.');
        }

        $this->issued_date = now()->format('Y-m-d');

        if ($id) {
            $this->ticketId = (int) $id;
            $t = ViolationTicket::findOrFail($id);
            $this->violator_name               = $t->violator_name;
            $this->violation_type              = $t->violation_type;
            $this->other_violation_description = $t->other_violation_description ?? '';
            $this->address                     = $t->address;
            $this->issued_date                 = $t->issued_date->format('Y-m-d');
            $this->remarks                     = $t->remarks ?? '';

            $this->ticket_number  = $t->ticket_number;
            $this->offense_number = $t->offense_number;
            $this->penalty_amount = (float) $t->penalty_amount;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'violator_name'               => trim($this->violator_name),
            'violation_type'              => $this->violation_type,
            'other_violation_description' => $this->violation_type === 'other'
                ? trim($this->other_violation_description) : null,
            'address'                     => trim($this->address),
            'issued_date'                 => $this->issued_date,
            'remarks'                     => $this->remarks !== '' ? $this->remarks : null,
        ];

        if ($this->ticketId) {
            $old = ViolationTicket::find($this->ticketId)?->toArray();
            ViolationTicket::findOrFail($this->ticketId)->update($data);
            logAudit('update', 'ViolationTicket', $this->ticketId, $old, $data);
            $message = 'Violation ticket updated.';
        } else {
            $priorCount = ViolationTicket::where('violator_name', 'ILIKE', trim($this->violator_name))->count();
            $offenseNumber = min($priorCount + 1, 3);

            $data['offense_number'] = $offenseNumber;
            $data['penalty_amount'] = ViolationTicket::fineForOffense($offenseNumber);
            $data['issued_by']      = session('auth_user_id');

            $year = Carbon::parse($this->issued_date)->format('Y');
            $attempts = 0;
            while (true) {
                $data['ticket_number'] = ViolationTicket::nextTicketNumber($year);
                try {
                    $new = ViolationTicket::create($data);
                    break;
                } catch (QueryException $e) {
                    if (++$attempts >= 5) {
                        throw $e;
                    }
                }
            }

            logAudit('create', 'ViolationTicket', $new->ticket_id, null, $data);
            $message = "Violation ticket {$data['ticket_number']} issued.";
        }

        Cache::forget('stats:violation_tickets');

        session()->flash('success', $message);
        return redirect()->route('violation-tickets.index');
    }

    public function render()
    {
        return view('livewire.violation-tickets.violation-ticket-form')
            ->extends('layouts.app');
    }
}
