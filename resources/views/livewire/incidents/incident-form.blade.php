<div>
    @section('title', ($incidentId ? 'Edit' : 'Report') . ' Incident — MENRO')
    @section('page-title', $incidentId ? 'Edit Incident' : 'Report Incident')
    @section('page-subtitle')
        <a href="{{ route('incidents.index') }}">Incidents</a>
        <span class="sep">›</span>
        {{ $incidentId ? 'Edit Incident' : 'Report Incident' }}
    @endsection

    <div class="max-w-3xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Incident Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Barangay <span class="text-red-500">*</span></label>
                        <select wire:model.defer="barangay_id" class="form-select">
                            <option value="">Select barangay…</option>
                            @foreach($barangays as $b)<option value="{{ $b->barangay_id }}">{{ $b->barangay_name }}</option>@endforeach
                        </select>
                        @error('barangay_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Incident Type <span class="text-red-500">*</span></label>
                        <select wire:model.defer="incident_type" class="form-select">
                            <option value="illegal_dumping">Illegal Dumping</option>
                            <option value="open_burning">Open Burning</option>
                            <option value="improper_disposal">Improper Disposal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Date Reported <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.defer="date_reported" class="form-input" />
                        @error('date_reported') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="status" class="form-select">
                            <option value="reported">Reported</option>
                            <option value="for_validation">For Validation</option>
                            <option value="under_investigation">Under Investigation</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Description <span class="text-red-500">*</span></label>
                        <textarea wire:model.defer="description" rows="3" class="form-input" placeholder="Describe the incident…"></textarea>
                        @error('description') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Location Details</label>
                        <input type="text" wire:model.defer="location_details" class="form-input" placeholder="Specific location or landmark" />
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Assignment & Resolution</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Assign To</label>
                        <select wire:model.defer="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($officers as $o)<option value="{{ $o->user_id }}">{{ $o->full_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Resolution Notes</label>
                        <textarea wire:model.defer="resolution_notes" rows="3" class="form-input" placeholder="Actions taken or resolution details…"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('incidents.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $incidentId ? 'Update Incident' : 'Submit Report' }}
                </button>
            </div>
        </form>
    </div>
</div>
