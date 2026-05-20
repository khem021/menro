<div>
    @section('title', 'Audit Trail — MENRO')
    @section('page-title', 'Audit Trail')

    <div style="height:calc(100vh - 72px - 3.5rem); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Filter bar --}}
        <div class="filter-bar flex-shrink-0">
            <div class="filter-row">
                <div class="filter-group"><span class="filter-label">Module</span>
                    <select wire:model="module" class="form-select w-40"><option value="">All Modules</option>@foreach($modules as $mod)<option value="{{ $mod }}">{{ $mod }}</option>@endforeach</select>
                </div>
                <div class="filter-group"><span class="filter-label">Action</span>
                    <select wire:model="action" class="form-select w-32"><option value="">All Actions</option>@foreach($actions as $act)<option value="{{ $act }}">{{ ucfirst($act) }}</option>@endforeach</select>
                </div>
                <div class="filter-group"><span class="filter-label">User</span>
                    <select wire:model="user_id" class="form-select w-44"><option value="">All Users</option>@foreach($users as $u)<option value="{{ $u->user_id }}">{{ $u->full_name }}</option>@endforeach</select>
                </div>
                <div class="filter-group"><span class="filter-label">From</span><input type="date" wire:model="date_from" class="form-input w-36" /></div>
                <div class="filter-group"><span class="filter-label">To</span><input type="date" wire:model="date_to" class="form-input w-36" /></div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="page-card flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto" style="scrollbar-width:none;-ms-overflow-style:none;">
                <table class="w-full">
                    <thead class="sticky top-0 z-10" style="background:var(--card-bg)"><tr>
                        <th class="table-header">Timestamp</th>
                        <th class="table-header">User</th>
                        <th class="table-header">Action</th>
                        <th class="table-header">Module</th>
                        <th class="table-header">Record ID</th>
                        <th class="table-header">IP Address</th>
                    </tr></thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr class="table-row">
                            <td class="table-cell">
                                <p class="text-xs font-medium" style="color:var(--text)">{{ $log->created_at->format('M d, Y') }}</p>
                                <p class="text-xs" style="color:var(--text-dim)">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="avatar-bg w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">{{ strtoupper(substr($log->user->full_name ?? 'S', 0, 1)) }}</div>
                                    <span class="text-sm font-medium" style="color:var(--text)">{{ $log->user->full_name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                @php $ac = match($log->action) {'create'=>'badge-green','update'=>'badge-blue','delete'=>'badge-red',default=>'badge-gray'}; @endphp
                                <span class="{{ $ac }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td class="table-cell text-sm font-medium">{{ $log->module }}</td>
                            <td class="table-cell font-mono text-xs">{{ $log->record_id ?? '—' }}</td>
                            <td class="table-cell font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background:var(--card-border)">
                                    <svg class="w-6 h-6" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-semibold" style="color:var(--text)">No audit logs found</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="px-5 py-3 flex-shrink-0 flex items-center justify-between" style="border-top:1px solid var(--card-border)">
                <p class="text-xs" style="color:var(--text-dim)">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries</p>
                {{ $logs->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
