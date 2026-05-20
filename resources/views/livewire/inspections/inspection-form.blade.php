<div>
    @section('title', ($inspectionId ? 'Edit' : 'Record') . ' Inspection — MENRO')
    @section('page-title', $inspectionId ? 'Edit Inspection' : 'Record Inspection')
    @section('page-subtitle')
        <a href="{{ route('inspections.index') }}">Inspections</a>
        <span class="sep">›</span>
        {{ $inspectionId ? 'Edit Inspection' : 'Record Inspection' }}
    @endsection

    <div class="max-w-3xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Inspection Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Generator <span class="text-red-500">*</span></label>
                        <select wire:model.defer="generator_id" class="form-select">
                            <option value="">Select generator…</option>
                            @foreach($generators as $g)<option value="{{ $g->generator_id }}">{{ $g->generator_name }}</option>@endforeach
                        </select>
                        @error('generator_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Inspection Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.defer="inspection_date" class="form-input" />
                        @error('inspection_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Inspector <span class="text-red-500">*</span></label>
                        <select wire:model.defer="inspector_id" class="form-select">
                            <option value="">Select inspector…</option>
                            @foreach($inspectors as $i)<option value="{{ $i->user_id }}">{{ $i->full_name }}</option>@endforeach
                        </select>
                        @error('inspector_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Compliance Assessment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Compliance Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="compliance_status" class="form-select">
                            <option value="compliant">Compliant</option>
                            <option value="warning">Warning</option>
                            <option value="for_follow_up">For Follow-up</option>
                            <option value="violation">Violation</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Segregation Score <span class="text-xs text-gray-400 normal-case">(0–100)</span></label>
                        <input type="number" min="0" max="100" wire:model.defer="segregation_score" class="form-input" placeholder="e.g. 85" />
                        @error('segregation_score') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Next Follow-up Date</label>
                        <input type="date" wire:model.defer="next_follow_up" class="form-input" />
                    </div>
                    <div class="md:col-span-3">
                        <label class="form-label">Findings / Remarks</label>
                        <textarea wire:model.defer="remarks" rows="3" class="form-input" placeholder="Describe inspection findings…"></textarea>
                    </div>
                    <div class="md:col-span-3">
                        <label class="form-label">Recommendations</label>
                        <textarea wire:model.defer="recommendation" rows="3" class="form-input" placeholder="Recommended corrective actions…"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inspections.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $inspectionId ? 'Update Inspection' : 'Record Inspection' }}
                </button>
            </div>
        </form>
    </div>
</div>
