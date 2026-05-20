<div>
    @section('title', ($violationId ? 'Edit' : 'Record') . ' Violation — MENRO')
    @section('page-title', $violationId ? 'Edit Violation' : 'Record Violation')
    @section('page-subtitle')
        <a href="{{ route('violations.index') }}">Violations</a>
        <span class="sep">›</span>
        {{ $violationId ? 'Edit Violation' : 'Record Violation' }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Violation Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Inspection <span class="text-red-500">*</span></label>
                        <select wire:model.defer="inspection_id" class="form-select">
                            <option value="">Select inspection…</option>
                            @foreach($inspections as $i)<option value="{{ $i->inspection_id }}">#{{ $i->inspection_id }} — {{ $i->wasteGenerator->generator_name ?? '?' }} ({{ \Carbon\Carbon::parse($i->inspection_date)->format('M d, Y') }})</option>@endforeach
                        </select>
                        @error('inspection_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Violation Type <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="violation_type" class="form-input" placeholder="e.g. Improper waste segregation" />
                        @error('violation_type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea wire:model.defer="description" rows="3" class="form-input" placeholder="Detailed description of the violation…"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Status & Penalties</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Severity <span class="text-red-500">*</span></label>
                        <select wire:model.defer="severity" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Penalty Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="penalty_status" class="form-select">
                            <option value="none">None</option>
                            <option value="warning_issued">Warning Issued</option>
                            <option value="penalty_pending">Penalty Pending</option>
                            <option value="penalty_applied">Penalty Applied</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Resolution Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="resolution_status" class="form-select">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="dismissed">Dismissed</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Resolved Date</label>
                        <input type="date" wire:model.defer="resolved_date" class="form-input" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('violations.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $violationId ? 'Update Violation' : 'Record Violation' }}
                </button>
            </div>
        </form>
    </div>
</div>
