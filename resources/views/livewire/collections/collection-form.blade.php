<div>
    @section('title', ($scheduleId ? 'Edit' : 'Schedule') . ' Collection — MENRO')
    @section('page-title', $scheduleId ? 'Edit Collection Schedule' : 'Schedule Collection')
    @section('page-subtitle')
        <a href="{{ route('collections.index') }}">Collections</a>
        <span class="sep">›</span>
        {{ $scheduleId ? 'Edit Schedule' : 'Schedule Collection' }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Schedule Details</h3>
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
                        <label class="form-label">Collection Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.defer="collection_date" class="form-input" />
                        @error('collection_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Waste Type <span class="text-red-500">*</span></label>
                        <select wire:model.defer="waste_type" class="form-select">
                            <option value="mixed">Mixed Waste</option>
                            <option value="biodegradable">Biodegradable</option>
                            <option value="recyclable">Recyclable</option>
                            <option value="residual">Residual</option>
                            <option value="hazardous">Hazardous</option>
                            <option value="special">Special Waste</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="missed">Missed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Assigned Team</label>
                        <input type="text" wire:model.defer="assigned_team" class="form-input" placeholder="Team name or members" />
                    </div>
                    <div>
                        <label class="form-label">Assigned Vehicle</label>
                        <input type="text" wire:model.defer="assigned_vehicle" class="form-input" placeholder="Plate number or vehicle ID" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Notes</label>
                        <textarea wire:model.defer="notes" rows="3" class="form-input" placeholder="Additional notes or instructions…"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('collections.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $scheduleId ? 'Update Schedule' : 'Create Schedule' }}
                </button>
            </div>
        </form>
    </div>
</div>
