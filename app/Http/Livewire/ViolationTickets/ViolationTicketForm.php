<?php

namespace App\Http\Livewire\ViolationTickets;

use App\Models\ViolationTicket;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
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

    /** Editable. Auto-filled with a preview on create unless the officer opts out. */
    public ?string $ticket_number = null;

    public bool $auto_ticket_number = true;

    /**
     * One of the three tiers in ViolationTicket::OFFENSE_LABELS. Pre-selected
     * from the ledger of prior tickets for the same name until the officer
     * picks one; the fine always follows from the schedule, never typed.
     */
    public ?int   $offense_number = null;
    public ?float $penalty_amount = null;

    /** Set once the officer picks an offense, so typing the name stops overriding it. */
    public bool $offense_chosen = false;

    protected function rules(): array
    {
        return [
            'ticket_number' => [
                Rule::requiredIf(fn () => !$this->auto_ticket_number),
                // Free text: officers copy the number off a paper booklet, whatever
                // its format. nextTicketNumber() skips anything not YYYY-digits.
                'nullable', 'string', 'max:20',
                // Deliberately NOT ->withoutTrashed(): Rule::unique queries the
                // table directly, so it counts archived tickets - which is what
                // nextTicketNumber()'s withTrashed() and the UNIQUE index both do.
                Rule::unique('violation_tickets', 'ticket_number')
                    ->ignore($this->ticketId, 'ticket_id'),
            ],
            'offense_number'              => ['required', 'integer', Rule::in(array_keys(ViolationTicket::OFFENSE_LABELS))],
            'violator_name'               => 'required|string|max:255',
            'violation_type'              => ['required', Rule::in(array_keys(ViolationTicket::VIOLATION_TYPES))],
            'other_violation_description' => 'required_if:violation_type,other|nullable|string|max:255',
            'address'                     => 'required|string|max:255',
            'issued_date'                 => 'required|date',
            'remarks'                     => 'nullable|string|max:1000',
        ];
    }

    public function mount($id = null)
    {
        if (!canAccess(...ROLES_STAFF())) {
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
            // A legacy count past three maps onto the tier the dropdown offers.
            $this->offense_number = ViolationTicket::offenseTier($t->offense_number);
            $this->penalty_amount = (float) $t->penalty_amount;
            $this->offense_chosen = true;

            // Show the stored number rather than replacing it with a preview.
            $this->auto_ticket_number = false;
        } else {
            $this->previewTicketNumber();
            $this->previewOffense();
        }
    }

    /**
     * Advisory only - save() re-derives the number at write time, because
     * another officer can issue a ticket between this render and that write.
     */
    private function previewTicketNumber(): void
    {
        $year = $this->issued_date !== ''
            ? Carbon::parse($this->issued_date)->format('Y')
            : now()->format('Y');

        $this->ticket_number = ViolationTicket::nextTicketNumber($year);
    }

    /**
     * Shows the officer which offense this will count as before they commit.
     *
     * This is the only feedback that a near-duplicate spelling - "Jamora, khem
     * Cyril" instead of "Jamora, Khem Cyril D." - resets the violator to a first
     * offense, because the counter matches the name exactly.
     */
    private function previewOffense(): void
    {
        $prior = ViolationTicket::whereRaw('LOWER(violator_name) = ?',
            [mb_strtolower(trim($this->violator_name))])->count();

        $this->offense_number = ViolationTicket::offenseTier($prior + 1);
        $this->penalty_amount = ViolationTicket::fineForOffense($this->offense_number);
    }

    public function updatedOffenseNumber(): void
    {
        // Not clamped here: save() validates it against the three choices.
        $this->offense_chosen = true;
        $this->penalty_amount = ViolationTicket::fineForOffense((int) $this->offense_number);
    }

    public function updatedAutoTicketNumber(): void
    {
        // Flipping back to auto re-fetches; flipping to manual keeps the
        // previewed number so the officer edits a sequence, not a blank field.
        if ($this->auto_ticket_number && !$this->ticketId) {
            $this->previewTicketNumber();
        }
    }

    public function updatedIssuedDate(): void
    {
        // save() takes the year from issued_date, so a date moved into another
        // year has to move the preview with it.
        if ($this->auto_ticket_number && !$this->ticketId) {
            $this->previewTicketNumber();
        }
    }

    public function updatedViolatorName(): void
    {
        if (!$this->ticketId && !$this->offense_chosen) {
            $this->previewOffense();
        }
    }

    public function save()
    {
        // mount() does not re-run on Livewire action requests, and route
        // middleware never sees them, so the check has to live here too.
        requireRole(...ROLES_STAFF());

        $this->validate();

        $data = [
            'violator_name'               => trim($this->violator_name),
            'violation_type'              => $this->violation_type,
            'other_violation_description' => $this->violation_type === 'other'
                ? trim($this->other_violation_description) : null,
            'address'                     => trim($this->address),
            'issued_date'                 => $this->issued_date,
            'remarks'                     => $this->remarks !== '' ? $this->remarks : null,
            // The officer's choice; the fine is looked up, never entered.
            'offense_number'              => (int) $this->offense_number,
            'penalty_amount'              => ViolationTicket::fineForOffense((int) $this->offense_number),
        ];

        if ($this->ticketId) {
            $data['ticket_number'] = trim($this->ticket_number);

            $old = ViolationTicket::find($this->ticketId)?->toArray();
            ViolationTicket::findOrFail($this->ticketId)->update($data);
            logAudit('update', 'ViolationTicket', $this->ticketId, $old, $data);
            $message = 'Violation ticket updated.';
        } else {
            $data['issued_by'] = session('auth_user_id');

            if ($this->auto_ticket_number) {
                $year = Carbon::parse($this->issued_date)->format('Y');
                $attempts = 0;
                while (true) {
                    $data['ticket_number'] = ViolationTicket::nextTicketNumber($year);
                    try {
                        $new = ViolationTicket::create($data);
                        break;
                    } catch (QueryException $e) {
                        // Only a clash on the ticket number is worth retrying
                        // (two people issuing at once). Anything else is real.
                        if (!$this->isTicketNumberClash($e) || ++$attempts >= 5) {
                            throw $e;
                        }
                    }
                }
            } else {
                // No retry loop on this path: retrying a number the officer
                // chose would clash forever. The Rule::unique check above
                // catches the ordinary case; this closes the gap between
                // validating and inserting, as a field error rather than a 500.
                $data['ticket_number'] = trim($this->ticket_number);

                try {
                    $new = ViolationTicket::create($data);
                } catch (QueryException $e) {
                    if (!$this->isTicketNumberClash($e)) {
                        throw $e;
                    }

                    $this->addError('ticket_number', 'That ticket number was just taken. Choose another.');

                    return;
                }
            }

            logAudit('create', 'ViolationTicket', $new->ticket_id, null, $data);
            $message = "Violation ticket {$data['ticket_number']} issued.";
        }

        session()->flash('success', $message);
        return redirect()->route('violation-tickets.index');
    }

    /** A unique-violation on ticket_number specifically, not any DB error. */
    private function isTicketNumberClash(QueryException $e): bool
    {
        return in_array($e->getCode(), ['23505', '23000'], true)
            && str_contains($e->getMessage(), 'ticket_number');
    }

    public function render()
    {
        return view('livewire.violation-tickets.violation-ticket-form')
            ->extends('layouts.app');
    }
}
