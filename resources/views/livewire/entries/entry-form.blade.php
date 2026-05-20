<div>
    @section('title', ($entryId ? 'Edit' : 'Record') . ' Waste Entry — MENRO')
    @section('page-title', $entryId ? 'Edit Waste Entry' : 'Record Waste Entry')
    @section('page-subtitle')
        <a href="{{ route('entries.index') }}">Waste Entries</a>
        <span class="sep">›</span>
        {{ $entryId ? 'Edit Entry' : 'Record Entry' }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Entry Details</h3>
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
                        <label class="form-label">Waste Category <span class="text-red-500">*</span></label>
                        <select wire:model.defer="category_id" class="form-select">
                            <option value="">Select category…</option>
                            @foreach($categories as $c)<option value="{{ $c->category_id }}">{{ $c->category_name }}</option>@endforeach
                        </select>
                        @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Entry Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.defer="entry_date" class="form-input" />
                        @error('entry_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" wire:model.defer="quantity" class="form-input" placeholder="0.00" />
                        @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Unit <span class="text-red-500">*</span></label>
                        <select wire:model.defer="unit" class="form-select">
                            <option value="kg">Kilogram (kg)</option>
                            <option value="ton">Ton</option>
                            <option value="liter">Liter</option>
                            <option value="cubic_meter">Cubic Meter (m³)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Remarks</label>
                        <textarea wire:model.defer="remarks" rows="3" class="form-input" placeholder="Optional notes or observations…"></textarea>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('entries.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $entryId ? 'Update Entry' : 'Record Entry' }}
                </button>
            </div>
        </form>
    </div>
</div>
