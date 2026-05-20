<div>
    @section('title', 'Settings — MENRO')
    @section('page-title', 'Settings')

    {{-- Flash --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="flash-success">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-3xl mx-auto">

        {{-- User card --}}
        <div class="form-section flex items-center gap-4 mb-6">
            <div class="relative flex-shrink-0 group">
                @if($user->avatar)
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}"
                         alt="{{ $user->full_name }}"
                         class="w-16 h-16 rounded-full object-cover shadow"
                         style="ring:2px solid var(--card-border)"/>
                @else
                    <div class="avatar-bg w-16 h-16 rounded-full flex items-center justify-center shadow">
                        <span class="text-2xl font-bold text-white">{{ strtoupper(substr($user->full_name, 0, 1)) }}</span>
                    </div>
                @endif
                {{-- Quick-change overlay --}}
                <button wire:click="$set('activeTab','avatar')"
                        class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
            <div>
                <p class="font-semibold text-base" style="color:var(--text)">{{ $user->full_name }}</p>
                <p class="text-sm" style="color:var(--text-muted)">{{ $user->role_name }}</p>
                <span class="{{ $user->status === 'active' ? 'badge-green' : 'badge-gray' }} mt-1">
                    <span class="w-1.5 h-1.5 rounded-full mr-0.5 {{ $user->status === 'active' ? 'bg-[#34d399]' : 'bg-[#7b8fad]' }}"></span>
                    {{ ucfirst($user->status) }}
                </span>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 mb-6 border-b overflow-x-auto" style="border-color:var(--card-border)">
            @foreach([
                ['key'=>'profile',  'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',                         'label'=>'Profile'],
                ['key'=>'avatar',   'icon'=>'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z', 'label'=>'Picture'],
                ['key'=>'password', 'icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'label'=>'Password'],
            ] as $t)
            <button wire:click="$set('activeTab','{{ $t['key'] }}')"
                class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors border-b-2 -mb-px
                    {{ $activeTab === $t['key'] ? 'tab-active' : 'border-transparent' }}"
                style="{{ $activeTab !== $t['key'] ? 'color:var(--text-muted)' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/>
                </svg>
                {{ $t['label'] }}
            </button>
            @endforeach
        </div>

        {{-- ── PROFILE TAB ─────────────────────────────────────────────────── --}}
        @if($activeTab === 'profile')
        <form wire:submit.prevent="saveProfile">
            <div class="form-section">
                <h3 class="form-section-title">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" wire:model.defer="full_name" class="form-input" placeholder="Juan Dela Cruz"/>
                        @error('full_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Username <span style="color:var(--danger)">*</span></label>
                        <input type="text" wire:model.defer="username" class="form-input" placeholder="jdelacruz" autocomplete="off"/>
                        @error('username') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" wire:model.defer="email" class="form-input" placeholder="user@menro.gov.ph"/>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Save Profile
                </button>
            </div>
        </form>
        @endif

        {{-- ── AVATAR TAB ───────────────────────────────────────────────────── --}}
        @if($activeTab === 'avatar')
        <div class="form-section">
            <h3 class="form-section-title">Profile Picture</h3>

            {{-- Current picture --}}
            <div class="flex items-center gap-5 mb-6">
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                         class="w-24 h-24 rounded-full object-cover shadow-md"
                         style="outline:2px solid var(--card-border)"/>
                    <div>
                        <p class="text-sm font-medium" style="color:var(--text)">New picture preview</p>
                        <p class="text-xs mt-1" style="color:var(--text-muted)">Click "Save Picture" to apply</p>
                    </div>
                @elseif($user->avatar)
                    <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->full_name }}"
                         class="w-24 h-24 rounded-full object-cover shadow-md"
                         style="outline:2px solid var(--card-border)"/>
                    <div>
                        <p class="text-sm font-medium" style="color:var(--text)">Current profile picture</p>
                        <button wire:click="removeAvatar"
                                wire:confirm="Remove your profile picture?"
                                class="mt-2 text-xs font-medium hover:underline" style="color:var(--danger)">
                            Remove picture
                        </button>
                    </div>
                @else
                    <div class="avatar-bg w-24 h-24 rounded-full flex items-center justify-center shadow-md">
                        <span class="text-4xl font-bold text-white">{{ strtoupper(substr($user->full_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color:var(--text)">No profile picture set</p>
                        <p class="text-xs mt-1" style="color:var(--text-muted)">Upload a JPG, PNG or WebP image (max 2 MB)</p>
                    </div>
                @endif
            </div>

            {{-- Upload area --}}
            <div x-data="{ dragging: false }"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                 :style="dragging ? 'border-color:var(--accent)' : 'border-color:var(--card-border)'"
                 class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors"
                 @click="$refs.fileInput.click()">
                <input type="file" x-ref="fileInput" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="hidden"/>
                <svg class="w-10 h-10 mx-auto mb-3" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm" style="color:var(--text-muted)">
                    <span class="font-semibold" style="color:var(--accent)">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs mt-1" style="color:var(--text-dim)">JPG, PNG, WebP — Max 2 MB</p>
                <div wire:loading wire:target="photo" class="mt-3 flex justify-center">
                    <svg class="w-5 h-5 animate-spin" style="color:var(--text-muted)" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            @error('photo') <p class="form-error mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button wire:click="saveAvatar" class="btn-primary" wire:loading.attr="disabled" wire:target="saveAvatar"
                    @if(!$photo) disabled style="opacity:0.5;cursor:not-allowed" @endif>
                <svg wire:loading.remove wire:target="saveAvatar" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading wire:target="saveAvatar" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Save Picture
            </button>
        </div>
        @endif

        {{-- ── PASSWORD TAB ─────────────────────────────────────────────────── --}}
        @if($activeTab === 'password')
        <form wire:submit.prevent="savePassword">
            <div class="form-section">
                <h3 class="form-section-title">Change Password</h3>
                <p class="text-xs mb-5" style="color:var(--text-muted)">Choose a strong password of at least 8 characters.</p>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="form-label">Current Password <span style="color:var(--danger)">*</span></label>
                        <input type="password" wire:model.defer="current_password" class="form-input"
                               autocomplete="current-password" placeholder="Enter your current password"/>
                        @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="form-label">New Password <span style="color:var(--danger)">*</span></label>
                            <input type="password" wire:model.defer="new_password" class="form-input"
                                   autocomplete="new-password" placeholder="Min 8 characters"/>
                            @error('new_password') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Confirm New Password <span style="color:var(--danger)">*</span></label>
                            <input type="password" wire:model.defer="new_password_confirmation" class="form-input"
                                   autocomplete="new-password" placeholder="Repeat new password"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Update Password
                </button>
            </div>
        </form>
        @endif


    </div>
</div>
