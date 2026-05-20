<div>
    @section('title', ($generatorId ? 'Edit' : 'Add') . ' Generator — MENRO')
    @section('page-title', $generatorId ? 'Edit Generator' : 'Add Generator')
    @section('page-subtitle')
        <a href="{{ route('generators.index') }}">Waste Generators</a>
        <span class="sep">›</span>
        {{ $generatorId ? 'Edit Generator' : 'Add Generator' }}
    @endsection

    <div class="max-w-3xl mx-auto">
        <form wire:submit.prevent="save">

            {{-- Basic Info --}}
            <div class="form-section">
                <h3 class="form-section-title">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Generator Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="generator_name" class="form-input" placeholder="Business or establishment name" />
                        @error('generator_name') <p class="form-error"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Generator Type <span class="text-red-500">*</span></label>
                        <select wire:model.defer="generator_type_id" class="form-select">
                            <option value="">Select type…</option>
                            @foreach($generatorTypes as $type)<option value="{{ $type->generator_type_id }}">{{ $type->type_name }}</option>@endforeach
                        </select>
                        @error('generator_type_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Barangay <span class="text-red-500">*</span></label>
                        <select wire:model.defer="barangay_id" class="form-select">
                            <option value="">Select barangay…</option>
                            @foreach($barangays as $b)<option value="{{ $b->barangay_id }}">{{ $b->barangay_name }}</option>@endforeach
                        </select>
                        @error('barangay_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Address</label>
                        <input type="text" wire:model.defer="address" class="form-input" placeholder="Street address or landmark" />
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="form-section">
                <h3 class="form-section-title">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Contact Person</label>
                        <input type="text" wire:model.defer="contact_person" class="form-input" placeholder="Full name" />
                    </div>
                    <div>
                        <label class="form-label">Contact Number</label>
                        <input type="text" wire:model.defer="contact_number" class="form-input" placeholder="+63 9XX XXX XXXX" />
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" wire:model.defer="email" class="form-input" placeholder="email@example.com" />
                    </div>
                </div>
            </div>

            {{-- Compliance & Status --}}
            <div class="form-section">
                <h3 class="form-section-title">Compliance & Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="form-label">Est. Daily Waste (kg)</label>
                        <input type="number" step="0.01" wire:model.defer="estimated_daily_waste_kg" class="form-input" placeholder="0.00" />
                    </div>
                    <div>
                        <label class="form-label">Compliance Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="compliance_status" class="form-select">
                            <option value="compliant">Compliant</option>
                            <option value="for_inspection">For Inspection</option>
                            <option value="non_compliant">Non-Compliant</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('generators.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $generatorId ? 'Update Generator' : 'Create Generator' }}
                </button>
            </div>
        </form>
    </div>
</div>
