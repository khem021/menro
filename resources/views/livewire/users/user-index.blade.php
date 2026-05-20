<div>
    @section('title', 'Users — MENRO')
    @section('page-title', 'User Management')

    @if(session('success'))<div class="flash-success" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="flash-error" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ session('error') }}</div>@endif

    <div style="height:calc(100vh - 72px - 3.5rem); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5 flex-shrink-0">
            <div class="stat-card">
                <div><p class="filter-label mb-0.5">Total Users</p><p class="text-2xl font-bold mt-1" style="color:var(--text)">{{ $stats['total'] }}</p></div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:var(--card-border)">
                    <svg class="w-4 h-4" style="color:var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="stat-card">
                <div><p class="filter-label mb-0.5">Active</p><p class="text-2xl font-bold mt-1" style="color:#34d399">{{ $stats['active'] }}</p></div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:rgba(52,211,153,0.10)">
                    <svg class="w-4 h-4" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div class="stat-card">
                <div><p class="filter-label mb-0.5">Inactive</p><p class="text-2xl font-bold mt-1" style="color:var(--text-muted)">{{ $stats['inactive'] }}</p></div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:var(--card-border)">
                    <svg class="w-4 h-4" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </div>
            <div class="stat-card">
                <div><p class="filter-label mb-0.5">Roles</p><p class="text-2xl font-bold mt-1" style="color:#60a5fa">{{ $stats['roles_count'] }}</p></div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:rgba(96,165,250,0.10)">
                    <svg class="w-4 h-4" style="color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="filter-bar flex-shrink-0">
            <div class="filter-row">
                <div class="filter-group flex-1 min-w-[180px]"><span class="filter-label">Search</span>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input wire:model.debounce.300ms="search" type="text" placeholder="Name, username, email…" class="form-input pl-9" />
                    </div>
                </div>
                <div class="filter-group"><span class="filter-label">Role</span>
                    <select wire:model="role_id" class="form-select w-44"><option value="">All Roles</option>@foreach($roles as $r)<option value="{{ $r->role_id }}">{{ $r->role_name }}</option>@endforeach</select>
                </div>
                <div class="filter-group"><span class="filter-label">Status</span>
                    <select wire:model="status" class="form-select w-36"><option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
                <div class="ml-auto flex items-end">
                    <a href="{{ route('users.create') }}" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add User
                    </a>
                </div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="page-card flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto" style="scrollbar-width:none;-ms-overflow-style:none;">
                <style>.usr-tbl::-webkit-scrollbar{display:none}</style>
                <table class="w-full">
                    <thead class="sticky top-0 z-10" style="background:var(--card-bg)"><tr>
                        <th class="table-header">User</th>
                        <th class="table-header">Username</th>
                        <th class="table-header">Email</th>
                        <th class="table-header">Role</th>
                        <th class="table-header">Status</th>
                        <th class="table-header text-right">Actions</th>
                    </tr></thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="table-row">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <div class="avatar-bg w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($user->full_name,0,1)) }}</div>
                                    <span class="font-semibold text-sm" style="color:var(--text)">{{ $user->full_name }}</span>
                                </div>
                            </td>
                            <td class="table-cell font-mono text-sm">{{ $user->username }}</td>
                            <td class="table-cell text-sm">{{ $user->email ?? '—' }}</td>
                            <td class="table-cell"><span class="badge-blue">{{ $user->role->role_name ?? '—' }}</span></td>
                            <td class="table-cell"><span class="{{ $user->status==='active'?'badge-green':'badge-gray' }}">{{ ucfirst($user->status) }}</span></td>
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('users.edit', $user->user_id) }}" class="btn-icon-edit" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @if($user->user_id !== session('auth_user_id'))
                                    <button wire:click="delete({{ $user->user_id }})" wire:confirm="Delete {{ $user->full_name }}?" class="btn-icon-del" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background:var(--card-border)">
                                    <svg class="w-6 h-6" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold" style="color:var(--text)">No users found</p>
                                    <p class="text-xs mt-1" style="color:var(--text-dim)">Adjust search or add a new user.</p>
                                </div>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
            <div class="px-5 py-3 flex-shrink-0 flex items-center justify-between" style="border-top:1px solid var(--card-border)">
                <p class="text-xs" style="color:var(--text-dim)">{{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users</p>
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
