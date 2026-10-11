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
     * Derived, not entered. The offense count comes from the ledger of prior
     * tickets for the same name, and the fine from the schedule, so neither is
     * a field anyone can type into.
     */
    public ?int   $offense_number = null;
    public ?float $penalty_amount = null;

    protected function rules(): array
    {
        return [
            'ticket_number' => [
                Rule::requiredIf(fn () => !$this->auto_ticket_number),
                'nullable', 'string', 'max:20',
                // nextTicketNumber() parses the sequence with (int) substr(), so a
                // free-text number inside a year prefix would make it reissue
                // numbers that already exist. {4,} keeps 2026-10001 legal.
                'regex:/^\d{4}-\d{4,}$/',
                // Deliberately NOT ->withoutTrashed(): Rule::unique queries the
                // table directly, so it counts archived tickets - which is what
                // nextTicketNumber()'s withTrashed() and the UNIQUE index both do.
                Rule::unique('violation_tickets', 'ticket_number')
                    ->ignore($this->ticketId, 'ticket_id'),
            ],
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
            $this->offense_number = $t->offense_number;
            $this->penalty_amount = (float) $t->penalty_amount;

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

        $this->offense_number = min($prior + 1, 255);
        $this->penalty_amount = ViolationTicket::fineForOffense($this->offense_number);
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
        if (!$this->ticketId) {
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
        ];

        if ($this->ticketId) {
            // Correcting a mistyped number is the main reason to edit a ticket
            // at all; offense and penalty stay derived and are not touched here.
            $data['ticket_number'] = trim($this->ticket_number);

            $old = ViolationTicket::find($this->ticketId)?->toArray();
            ViolationTicket::findOrFail($this->ticketId)->update($data);
            logAudit('update', 'ViolationTicket', $this->ticketId, $old, $data);
            $message = 'Violation ticket updated.';
        } else {
            $priorCount = ViolationTicket::whereRaw('LOWER(violator_name) = ?', [mb_strtolower(trim($this->violator_name))])->count();
            // Store the true count so a repeat offender's ticket says so; the 255
            // clamp is only the unsignedTinyInteger ceiling. fineForOffense()
            // caps the amount at the third tier.
            $offenseNumber = min($priorCount + 1, 255);

            $data['offense_number'] = $offenseNumber;
            $data['penalty_amount'] = ViolationTicket::fineForOffense($offenseNumber);
            $data['issued_by']      = session('auth_user_id');

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
