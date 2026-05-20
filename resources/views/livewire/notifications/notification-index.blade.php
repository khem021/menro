<div>
    @section('title', 'Notifications — MENRO')
    @section('page-title', 'Notifications')

    @if(session('success'))
    <div class="flash-success" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
            <span class="badge-red">{{ $unreadCount }} unread</span>
            @else
            <span class="text-xs" style="color:var(--text-dim)">All caught up</span>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($unreadCount > 0)
            <button wire:click="markAllRead" class="btn-secondary text-xs">Mark all as read</button>
            @endif
            @if($notifications->total() > 0)
            <button wire:click="deleteAll"
                    wire:confirm="Delete all your notifications? This cannot be undone."
                    class="btn-danger text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete All
            </button>
            @endif
            @if(canAccess('System Administrator', 'MENRO Officer'))
            <button wire:click="$set('showForm', true)" class="btn-primary text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send Notification
            </button>
            @endif
        </div>
    </div>

    {{-- Send Form --}}
    @if($showForm)
    <div class="form-section mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="form-section-title mb-0">Send Notification</h3>
            <button wire:click="$set('showForm', false)" class="btn-icon-del"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="form-label">Title</label>
                <input type="text" wire:model.defer="form_title" class="form-input" placeholder="Notification title" />
                @error('form_title') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Message</label>
                <textarea wire:model.defer="form_message" rows="3" class="form-input" placeholder="Write the notification message…"></textarea>
                @error('form_message') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type</label>
                <select wire:model.defer="form_type" class="form-select">
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                    <option value="danger">Danger / Alert</option>
                </select>
            </div>
            <div>
                <label class="form-label">Send To</label>
                <select wire:model="form_target" class="form-select">
                    <option value="all">All Users</option>
                    <option value="role">Specific Role</option>
                    <option value="user">Specific User</option>
                </select>
            </div>
            @if($form_target === 'role')
            <div class="md:col-span-2">
                <label class="form-label">Role</label>
                <select wire:model.defer="form_role" class="form-select"><option value="">— Select role —</option>@foreach($roles as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach</select>
            </div>
            @elseif($form_target === 'user')
            <div class="md:col-span-2">
                <label class="form-label">User</label>
                <select wire:model.defer="form_user_id" class="form-select"><option value="">— Select user —</option>@foreach($users as $u)<option value="{{ $u->user_id }}">{{ $u->full_name }} ({{ $u->username }})</option>@endforeach</select>
            </div>
            @endif
        </div>
        <div class="flex justify-end gap-2 mt-4 pt-4 border-t" style="border-color:var(--card-border)">
            <button wire:click="$set('showForm', false)" class="btn-secondary text-xs">Cancel</button>
            <button wire:click="sendNotification" class="btn-primary text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send
            </button>
        </div>
    </div>
    @endif

    {{-- Notification List --}}
    <div class="space-y-2.5">
        @forelse($notifications as $notif)
        @php $typeColors = ['danger'=>['border'=>'border-l-red-500','icon'=>'text-red-500','bg'=>'bg-red-500/10'],'warning'=>['border'=>'border-l-amber-500','icon'=>'text-amber-500','bg'=>'bg-amber-500/10'],'success'=>['border'=>'border-l-emerald-500','icon'=>'text-emerald-500','bg'=>'bg-emerald-500/10'],'info'=>['border'=>'border-l-blue-500','icon'=>'text-blue-500','bg'=>'bg-blue-500/10']];
        $tc = $typeColors[$notif->type] ?? $typeColors['info']; @endphp
        <div class="card p-4 flex items-start gap-4 transition-all {{ !$notif->is_read ? 'border-l-4 '.$tc['border'] : 'opacity-75' }}">
            <div class="w-8 h-8 rounded-lg {{ $tc['bg'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 {{ $tc['icon'] }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-0.5">
                    <p class="text-sm font-semibold" style="color:var(--text)">
                        {{ $notif->title }}
                        @if(!$notif->is_read)<span class="ml-1.5 inline-block w-2 h-2 rounded-full bg-[#34d399] align-middle"></span>@endif
                    </p>
                    <span class="text-xs flex-shrink-0 whitespace-nowrap" style="color:var(--text-dim)">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm" style="color:var(--text-muted)">{{ $notif->message }}</p>
                <div class="mt-2 flex items-center gap-3">
                    @php $badge = match($notif->type) {'danger'=>'badge-red','warning'=>'badge-yellow','success'=>'badge-green',default=>'badge-blue'}; @endphp
                    <span class="{{ $badge }}">{{ ucfirst($notif->type) }}</span>
                    @if(!$notif->is_read)
                    <button wire:click="markRead({{ $notif->notification_id }})" class="text-xs font-medium hover:underline" style="color:var(--clr-p)">Mark as read</button>
                    @else
                    <span class="text-xs" style="color:var(--text-dim)">Read {{ $notif->read_at ? \Carbon\Carbon::parse($notif->read_at)->diffForHumans() : '' }}</span>
                    @endif
                    <button wire:click="deleteNotification({{ $notif->notification_id }})"
                            wire:confirm="Delete this notification?"
                            class="text-xs font-medium hover:underline ml-1" style="color:var(--danger)">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="card p-16 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background:var(--card-border)">
                    <svg class="w-7 h-7" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div><p class="text-sm font-semibold" style="color:var(--text)">No notifications</p><p class="text-xs mt-1" style="color:var(--text-dim)">You're all caught up! Notifications will appear here.</p></div>
            </div>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
