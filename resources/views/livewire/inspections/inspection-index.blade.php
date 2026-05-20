<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Inspections — MENRO')
    @section('page-title', 'Inspections')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ $stats['total'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:#1c2d4a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Compliant</div><div style="font-size:1.375rem;font-weight:700;color:#34d399;line-height:1.1;margin:0.2rem 0;">{{ $stats['compliant'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Violations</div><div style="font-size:1.375rem;font-weight:700;color:#f87171;line-height:1.1;margin:0.2rem 0;">{{ $stats['violations'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(248,113,113,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Pending Follow-up</div><div style="font-size:1.375rem;font-weight:700;color:#FDB813;line-height:1.1;margin:0.2rem 0;">{{ $stats['follow_ups'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(253,184,19,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#FDB813" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Generator</span>
                <select wire:model="generator_id" class="form-select" style="width:12rem;"><option value="">All Generators</option>@foreach($generators as $g)<option value="{{ $g->generator_id }}">{{ $g->generator_name }}</option>@endforeach</select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Compliance</span>
                <select wire:model="compliance_status" class="form-select" style="width:10rem;"><option value="">All</option><option value="compliant">Compliant</option><option value="warning">Warning</option><option value="for_follow_up">For Follow-up</option><option value="violation">Violation</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">From</span>
                <input type="date" wire:model="date_from" class="form-input" style="width:9rem;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">To</span>
                <input type="date" wire:model="date_to" class="form-input" style="width:9rem;" />
            </div>
            <a href="{{ route('inspections.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Inspection</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.insp-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="insp-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Date</th>
                        <th class="table-header">Generator</th>
                        <th class="table-header">Inspector</th>
                        <th class="table-header">Compliance</th>
                        <th class="table-header" style="text-align:center;">Score</th>
                        <th class="table-header">Follow-up</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspections as $insp)
                    <tr class="table-row">
                        <td class="table-cell" style="white-space:nowrap;">
                            <div style="font-weight:500;color:var(--text);font-size:0.8125rem;">{{ \Carbon\Carbon::parse($insp->inspection_date)->format('M d, Y') }}</div>
                        </td>
                        <td class="table-cell" style="font-weight:600;color:var(--text);font-size:0.8125rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($insp->wasteGenerator->generator_name ?? '—', 28) }}</td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $insp->inspector->full_name ?? '—' }}</td>
                        <td class="table-cell">
                            @php $cc = match($insp->compliance_status) {'compliant'=>'badge-green','warning'=>'badge-yellow','for_follow_up'=>'badge-yellow','violation'=>'badge-red',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $cc }}">{{ ucfirst(str_replace('_',' ',$insp->compliance_status)) }}</span>
                        </td>
                        <td class="table-cell" style="text-align:center;">
                            @if($insp->segregation_score !== null)
                            @php $sc = $insp->segregation_score >= 75 ? '#34d399' : ($insp->segregation_score >= 50 ? '#FDB813' : '#f87171'); @endphp
                            <span style="font-size:0.8125rem;font-weight:700;color:{{ $sc }};">{{ $insp->segregation_score }}</span><span style="font-size:0.6875rem;color:var(--text-muted);">/100</span>
                            @else<span style="color:var(--text-muted);">—</span>@endif
                        </td>
                        <td class="table-cell" style="font-size:0.8125rem;color:{{ ($insp->next_follow_up && \Carbon\Carbon::parse($insp->next_follow_up)->isPast()) ? '#f87171' : 'var(--text-muted)' }};white-space:nowrap;">
                            {{ $insp->next_follow_up ? \Carbon\Carbon::parse($insp->next_follow_up)->format('M d, Y') : '—' }}
                        </td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('inspections.edit', $insp->inspection_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $insp->inspection_id }})" wire:confirm="Delete this inspection?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No inspections found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or record a new inspection.</div><a href="{{ route('inspections.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Add Inspection</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inspections->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $inspections->firstItem() }}–{{ $inspections->lastItem() }} of {{ $inspections->total() }} inspections</span>
            {{ $inspections->links() }}
        </div>
        @endif
    </div>
</div>
