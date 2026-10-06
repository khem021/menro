<div>
    @section('title', ($ticketId ? 'Edit' : 'Issue') . ' Violation Ticket — MENRO')
    @section('page-title', $ticketId ? 'Edit Violation Ticket' : 'Issue Violation Ticket')
    @section('page-subtitle')
        <a href="{{ route('violation-tickets.index') }}">Violation</a>
        <span class="sep">›</span>
        {{ $ticketId ? 'Edit Ticket' : 'Issue Ticket' }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form wire:submit.prevent="save">

            @if($ticketId)
            <div class="form-section">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--card-border);">
                    <h3 class="form-section-title" style="margin:0;padding:0;border:none;">Ticket Information (auto-generated)</h3>
                    <a href="{{ route('violation-tickets.receipt', $ticketId) }}" target="_blank" class="btn-secondary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>Print Receipt</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Ticket Number</label>
                        <div class="form-input" style="background:var(--card-border);cursor:not-allowed;">{{ $ticket_number }}</div>
                    </div>
                    <div>
                        <label class="form-label">Offense</label>
                        <div class="form-input" style="background:var(--card-border);cursor:not-allowed;">{{ \App\Models\ViolationTicket::offenseLabel($offense_number) }}</div>
                    </div>
                    <div>
                        <label class="form-label">Penalty</label>
                        <div class="form-input" style="background:var(--card-border);cursor:not-allowed;">₱{{ number_format($penalty_amount, 2) }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-section">
                <h3 class="form-section-title">Violator Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Name of Violator <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="violator_name" class="form-input" placeholder="LastName, FirstName M." />
                        @error('violator_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="address" class="form-input" placeholder="House/Street, Barangay" />
                        @error('address') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Violation Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Type of Violation <span class="text-red-500">*</span></label>
                        <select wire:model="violation_type" class="form-select">
                            <option value="">Select type…</option>
                            <option value="littering">Littering</option>
                            <option value="dumping">Throwing/Dumping of Waste in Public</option>
                            <option value="burning">Burning of Solid Waste</option>
                            <option value="no_segregation">No Segregation of Waste</option>
                            <option value="other">Other Violations</option>
                        </select>
                        @error('violation_type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Date Issued <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.defer="issued_date" class="form-input" />
                        @error('issued_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    @if($violation_type === 'other')
                    <div class="md:col-span-2">
                        <label class="form-label">Specify Other Violation <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="other_violation_description" class="form-input" placeholder="Describe the violation…" />
                        @error('other_violation_description') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    @endif
                    <div class="md:col-span-2">
                        <label class="form-label">Remarks</label>
                        <textarea wire:model.defer="remarks" rows="3" class="form-input" placeholder="Additional notes…"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('violation-tickets.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $ticketId ? 'Update Ticket' : 'Issue Ticket' }}
                </button>
            </div>
        </form>
    </div>
</div>
