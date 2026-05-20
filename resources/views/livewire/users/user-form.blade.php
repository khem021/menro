<div>
    @section('title', ($userId ? 'Edit' : 'Add') . ' User — MENRO')
    @section('page-title', $userId ? 'Edit User' : 'Add User')

    <div class="max-w-2xl mx-auto">
        <form wire:submit.prevent="save">
            <div class="form-section">
                <h3 class="form-section-title">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="full_name" class="form-input" placeholder="Juan Dela Cruz" />
                        @error('full_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Username <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="username" class="form-input" placeholder="jdelacruz" autocomplete="off" />
                        @error('username') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" wire:model.defer="email" class="form-input" placeholder="user@menro.gov.ph" />
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Password</h3>
                @if($userId)<p class="text-xs mb-4" style="color:var(--text-muted)">Leave blank to keep the current password.</p>@endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">{{ $userId ? 'New Password' : 'Password' }} {{ !$userId ? '*' : '' }}</label>
                        <input type="password" wire:model.defer="password" class="form-input" autocomplete="new-password" placeholder="{{ $userId ? 'Leave blank to keep' : 'Min 8 characters' }}" />
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Confirm Password</label>
                        <input type="password" wire:model.defer="password_confirmation" class="form-input" autocomplete="new-password" placeholder="Repeat password" />
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Access & Role</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select wire:model.defer="role_id" class="form-select">
                            <option value="">Select role…</option>
                            @foreach($roles as $r)<option value="{{ $r->role_id }}">{{ $r->role_name }}</option>@endforeach
                        </select>
                        @error('role_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Account Status <span class="text-red-500">*</span></label>
                        <select wire:model.defer="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $userId ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</div>
