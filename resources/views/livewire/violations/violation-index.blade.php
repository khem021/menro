<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Violations — MENRO')
    @section('page-title', 'Violations')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ $stats['total'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:#1c2d4a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Open</div><div style="font-size:1.375rem;font-weight:700;color:#f87171;line-height:1.1;margin:0.2rem 0;">{{ $stats['open'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(248,113,113,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Critical Open</div><div style="font-size:1.375rem;font-weight:700;color:#fb923c;line-height:1.1;margin:0.2rem 0;">{{ $stats['critical'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(251,146,60,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Resolved</div><div style="font-size:1.375rem;font-weight:700;color:#34d399;line-height:1.1;margin:0.2rem 0;">{{ $stats['resolved'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Severity</span>
                <select wire:model="severity" class="form-select" style="width:9rem;"><option value="">All Severities</option><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Resolution</span>
                <select wire:model="resolution_status" class="form-select" style="width:10rem;"><option value="">All Statuses</option><option value="open">Open</option><option value="in_progress">In Progress</option><option value="resolved">Resolved</option><option value="dismissed">Dismissed</option></select>
            </div>
            <a href="{{ route('violations.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Violation</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.vio-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="vio-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Generator</th>
                        <th class="table-header">Violation Type</th>
                        <th class="table-header">Severity</th>
                        <th class="table-header">Penalty</th>
                        <th class="table-header">Resolution</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($violations as $v)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div style="font-weight:600;color:var(--text);font-size:0.8125rem;">{{ $v->inspection?->wasteGenerator?->generator_name ?? '—' }}</div>
                            <div style="font-size:0.6875rem;color:var(--text-muted);">Inspection #{{ $v->inspection_id }}</div>
                        </td>
                        <td class="table-cell" style="color:var(--text);font-size:0.8125rem;">{{ ucfirst(str_replace('_',' ',$v->violation_type)) }}</td>
                        <td class="table-cell">
                            @php $sc = match($v->severity) {'critical'=>'badge-red','high'=>'badge-red','medium'=>'badge-yellow','low'=>'badge-blue',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $sc }}">{{ ucfirst($v->severity) }}</span>
                        </td>
                        <td class="table-cell">
                            @php $pc = match($v->penalty_status ?? 'none') {'penalty_applied'=>'badge-red','penalty_pending'=>'badge-yellow','warning_issued'=>'badge-yellow','none'=>'badge-gray',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $pc }}">{{ ucfirst(str_replace('_',' ',$v->penalty_status ?? 'none')) }}</span>
                        </td>
                        <td class="table-cell">
                            @php $rc = match($v->resolution_status) {'resolved'=>'badge-green','in_progress'=>'badge-blue','dismissed'=>'badge-gray','open'=>'badge-red',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $rc }}">{{ ucfirst(str_replace('_',' ',$v->resolution_status ?? 'open')) }}</span>
                        </td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('violations.edit', $v->violation_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $v->violation_id }})" wire:confirm="Delete this violation?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No violations found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or record a new violation.</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($violations->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $violations->firstItem() }}–{{ $violations->lastItem() }} of {{ $violations->total() }} violations</span>
            {{ $violations->links() }}
        </div>
        @endif
    </div>
</div>
