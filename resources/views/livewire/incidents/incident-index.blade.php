<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Incidents — MENRO')
    @section('page-title', 'Incidents')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total Incidents</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ $stats['total'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:#1c2d4a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Open / Pending</div><div style="font-size:1.375rem;font-weight:700;color:#FDB813;line-height:1.1;margin:0.2rem 0;">{{ $stats['open'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(253,184,19,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#FDB813" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Under Investigation</div><div style="font-size:1.375rem;font-weight:700;color:#60a5fa;line-height:1.1;margin:0.2rem 0;">{{ $stats['under_inv'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Resolved</div><div style="font-size:1.375rem;font-weight:700;color:#34d399;line-height:1.1;margin:0.2rem 0;">{{ $stats['resolved'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Type</span>
                <select wire:model="incident_type" class="form-select" style="width:11rem;"><option value="">All Types</option><option value="illegal_dumping">Illegal Dumping</option><option value="open_burning">Open Burning</option><option value="improper_disposal">Improper Disposal</option><option value="other">Other</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Status</span>
                <select wire:model="status" class="form-select" style="width:11rem;"><option value="">All Statuses</option><option value="reported">Reported</option><option value="for_validation">For Validation</option><option value="under_investigation">Under Investigation</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Barangay</span>
                <select wire:model="barangay_id" class="form-select" style="width:11rem;"><option value="">All Barangays</option>@foreach($barangays as $b)<option value="{{ $b->barangay_id }}">{{ $b->barangay_name }}</option>@endforeach</select>
            </div>
            <a href="{{ route('incidents.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Report Incident</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.inc-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="inc-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Date</th>
                        <th class="table-header">Barangay</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Description</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Assigned To</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $inc)
                    <tr class="table-row">
                        <td class="table-cell" style="white-space:nowrap;">
                            <div style="font-weight:500;color:var(--text);font-size:0.8125rem;">{{ \Carbon\Carbon::parse($inc->date_reported)->format('M d, Y') }}</div>
                            <div style="font-size:0.6875rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($inc->date_reported)->diffForHumans() }}</div>
                        </td>
                        <td class="table-cell" style="font-weight:500;color:var(--text);font-size:0.8125rem;">{{ $inc->barangay->barangay_name ?? '—' }}</td>
                        <td class="table-cell">
                            @php $tc = match($inc->incident_type) {'illegal_dumping'=>'badge-red','open_burning'=>'badge-red','improper_disposal'=>'badge-yellow',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $tc }}">{{ ucfirst(str_replace('_', ' ', $inc->incident_type)) }}</span>
                        </td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($inc->description, 60) }}</td>
                        <td class="table-cell">
                            @php $sc = match($inc->status) {'resolved'=>'badge-green','under_investigation'=>'badge-blue','for_validation'=>'badge-yellow','reported'=>'badge-yellow','closed'=>'badge-gray',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                        </td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $inc->assignee->full_name ?? '—' }}</td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('incidents.edit', $inc->incident_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $inc->incident_id }})" wire:confirm="Delete this incident report?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No incidents found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust your filters or report a new incident.</div><a href="{{ route('incidents.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Report Incident</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($incidents->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $incidents->firstItem() }}–{{ $incidents->lastItem() }} of {{ $incidents->total() }} incidents</span>
            {{ $incidents->links() }}
        </div>
        @endif
    </div>
</div>
