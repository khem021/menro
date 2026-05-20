<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Compliance — MENRO')
    @section('page-title', 'Compliance & Enforcement')

    {{-- flash handled by global toast --}}

    {{-- ── Enforcement Pipeline ── --}}
    <div style="display:grid;grid-template-columns:1fr auto 1fr auto 1fr auto 1fr;align-items:stretch;gap:0;flex-shrink:0;border-radius:0.75rem;overflow:hidden;border:1px solid var(--card-border);">

        {{-- Step 1: Inspect --}}
        <button wire:click="setTab('inspections')"
                style="padding:0.625rem 0.875rem;background:{{ $tab==='inspections' ? 'rgba(96,165,250,0.12)' : 'var(--card-bg)' }};border:none;cursor:pointer;text-align:left;border-bottom:2px solid {{ $tab==='inspections' ? '#60a5fa' : 'transparent' }};transition:all .15s;display:flex;align-items:center;gap:0.625rem;">
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(96,165,250,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            </div>
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:{{ $tab==='inspections' ? '#60a5fa' : 'var(--text-dim)' }};">Step 1 · Inspect</div>
                <div style="font-size:1.125rem;font-weight:700;color:{{ $tab==='inspections' ? '#60a5fa' : 'var(--text)' }};line-height:1.1;">{{ $pipeline['insp_total'] }}</div>
                <div style="font-size:0.6rem;color:var(--text-muted);margin-top:1px;">
                    <span style="color:#34d399;">{{ $pipeline['insp_comply'] }} compliant</span>
                    @if($pipeline['insp_pending'] > 0) · <span style="color:#FDB813;">{{ $pipeline['insp_pending'] }} pending</span>@endif
                </div>
            </div>
        </button>

        {{-- Arrow --}}
        <div style="display:flex;align-items:center;justify-content:center;background:var(--card-bg);padding:0 0.5rem;border-left:1px solid var(--card-border);border-right:1px solid var(--card-border);">
            <svg width="14" height="14" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>

        {{-- Step 2: Violations --}}
        <button wire:click="setTab('violations')"
                style="padding:0.625rem 0.875rem;background:{{ $tab==='violations' ? 'rgba(248,113,113,0.1)' : 'var(--card-bg)' }};border:none;cursor:pointer;text-align:left;border-bottom:2px solid {{ $tab==='violations' ? '#f87171' : 'transparent' }};transition:all .15s;display:flex;align-items:center;gap:0.625rem;">
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(248,113,113,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:{{ $tab==='violations' ? '#f87171' : 'var(--text-dim)' }};">Step 2 · Enforce</div>
                <div style="font-size:1.125rem;font-weight:700;color:{{ $tab==='violations' ? '#f87171' : 'var(--text)' }};line-height:1.1;">{{ $pipeline['vio_open'] }} <span style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">open</span></div>
                <div style="font-size:0.6rem;color:var(--text-muted);margin-top:1px;">
                    {{ $pipeline['vio_total'] }} total
                    @if($pipeline['vio_critical'] > 0) · <span style="color:#f87171;">{{ $pipeline['vio_critical'] }} critical</span>@endif
                </div>
            </div>
        </button>

        {{-- Arrow --}}
        <div style="display:flex;align-items:center;justify-content:center;background:var(--card-bg);padding:0 0.5rem;border-left:1px solid var(--card-border);border-right:1px solid var(--card-border);">
            <svg width="14" height="14" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>

        {{-- Step 3: Incidents --}}
        <button wire:click="setTab('incidents')"
                style="padding:0.625rem 0.875rem;background:{{ $tab==='incidents' ? 'rgba(253,184,19,0.1)' : 'var(--card-bg)' }};border:none;cursor:pointer;text-align:left;border-bottom:2px solid {{ $tab==='incidents' ? '#FDB813' : 'transparent' }};transition:all .15s;display:flex;align-items:center;gap:0.625rem;">
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(253,184,19,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="#FDB813" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:{{ $tab==='incidents' ? '#FDB813' : 'var(--text-dim)' }};">Step 3 · Respond</div>
                <div style="font-size:1.125rem;font-weight:700;color:{{ $tab==='incidents' ? '#FDB813' : 'var(--text)' }};line-height:1.1;">{{ $pipeline['inc_active'] }} <span style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">active</span></div>
                <div style="font-size:0.6rem;color:var(--text-muted);margin-top:1px;">{{ $pipeline['inc_total'] }} total reported</div>
            </div>
        </button>

        {{-- Arrow --}}
        <div style="display:flex;align-items:center;justify-content:center;background:var(--card-bg);padding:0 0.5rem;border-left:1px solid var(--card-border);border-right:1px solid var(--card-border);">
            <svg width="14" height="14" fill="none" stroke="var(--text-dim)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>

        {{-- Step 4: Resolved (display only) --}}
        <div style="padding:0.625rem 0.875rem;background:var(--card-bg);display:flex;align-items:center;gap:0.625rem;">
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(52,211,153,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Step 4 · Resolved</div>
                <div style="font-size:1.125rem;font-weight:700;color:#34d399;line-height:1.1;">{{ $pipeline['inc_resolved'] }}</div>
                <div style="font-size:0.6rem;color:var(--text-muted);margin-top:1px;">incidents closed</div>
            </div>
        </div>

    </div>

    {{-- ── Filters + Add Button ── --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">

            @if($tab === 'inspections')
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Generator</span>
                <select wire:model="insp_generator_id" class="form-select" style="width:12rem;"><option value="">All Generators</option>@foreach($generators as $g)<option value="{{ $g->generator_id }}">{{ $g->generator_name }}</option>@endforeach</select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Compliance</span>
                <select wire:model="insp_compliance_status" class="form-select" style="width:10rem;"><option value="">All</option><option value="compliant">Compliant</option><option value="warning">Warning</option><option value="for_follow_up">For Follow-up</option><option value="violation">Violation</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">From</span>
                <input type="date" wire:model="insp_date_from" class="form-input" style="width:9rem;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">To</span>
                <input type="date" wire:model="insp_date_to" class="form-input" style="width:9rem;" />
            </div>
            <a href="{{ route('inspections.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Inspection</a>

            @elseif($tab === 'violations')
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Severity</span>
                <select wire:model="vio_severity" class="form-select" style="width:9rem;"><option value="">All Severities</option><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Resolution</span>
                <select wire:model="vio_resolution_status" class="form-select" style="width:10rem;"><option value="">All Statuses</option><option value="open">Open</option><option value="in_progress">In Progress</option><option value="resolved">Resolved</option><option value="dismissed">Dismissed</option></select>
            </div>
            <a href="{{ route('violations.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>File Violation</a>

            @elseif($tab === 'incidents')
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Type</span>
                <select wire:model="inc_type" class="form-select" style="width:11rem;"><option value="">All Types</option><option value="illegal_dumping">Illegal Dumping</option><option value="open_burning">Open Burning</option><option value="improper_disposal">Improper Disposal</option><option value="other">Other</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Status</span>
                <select wire:model="inc_status" class="form-select" style="width:11rem;"><option value="">All Statuses</option><option value="reported">Reported</option><option value="for_validation">For Validation</option><option value="under_investigation">Under Investigation</option><option value="resolved">Resolved</option><option value="closed">Closed</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Barangay</span>
                <select wire:model="inc_barangay_id" class="form-select" style="width:11rem;"><option value="">All Barangays</option>@foreach($barangays as $b)<option value="{{ $b->barangay_id }}">{{ $b->barangay_name }}</option>@endforeach</select>
            </div>
            <a href="{{ route('incidents.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Report Incident</a>
            @endif

        </div>
    </div>

    {{-- ── Table ── --}}
    <style>.comp-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">

        {{-- ===== INSPECTIONS TABLE ===== --}}
        @if($tab === 'inspections')
        <div class="comp-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
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
                        <td class="table-cell" style="font-weight:600;color:var(--text);font-size:0.8125rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($insp->wasteGenerator->generator_name ?? '—', 30) }}</td>
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
                                <button wire:click="deleteInspection({{ $insp->inspection_id }})" wire:confirm="Delete this inspection?" class="btn-icon btn-icon-del"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="#60a5fa" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No inspections found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust your filters or record a new inspection.</div><a href="{{ route('inspections.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Add Inspection</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inspections && $inspections->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $inspections->firstItem() }}–{{ $inspections->lastItem() }} of {{ $inspections->total() }} inspections</span>
            {{ $inspections->links() }}
        </div>
        @endif

        {{-- ===== VIOLATIONS TABLE ===== --}}
        @elseif($tab === 'violations')
        <div class="comp-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
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
                            <div style="font-weight:600;color:var(--text);font-size:0.8125rem;">{{ $v->inspection->wasteGenerator->generator_name ?? '—' }}</div>
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
                                @if(in_array($v->resolution_status, ['open','in_progress']))
                                @php $nextLabel = $v->resolution_status === 'open' ? 'In Progress' : 'Resolve'; @endphp
                                <button wire:click="advanceViolation({{ $v->violation_id }})"
                                        title="Mark as {{ $nextLabel }}"
                                        style="display:inline-flex;align-items:center;gap:0.2rem;padding:0.2rem 0.5rem;font-size:0.6875rem;font-weight:600;border-radius:0.375rem;border:1px solid {{ $v->resolution_status==='open' ? '#3b82f6' : '#34d399' }};background:{{ $v->resolution_status==='open' ? 'rgba(59,130,246,0.1)' : 'rgba(52,211,153,0.1)' }};color:{{ $v->resolution_status==='open' ? '#60a5fa' : '#34d399' }};cursor:pointer;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    {{ $nextLabel }}
                                </button>
                                @endif
                                <a href="{{ route('violations.edit', $v->violation_id) }}" class="btn-icon btn-icon-edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="deleteViolation({{ $v->violation_id }})" wire:confirm="Delete this violation?" class="btn-icon btn-icon-del"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="#f87171" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No violations found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or file a new violation.</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($violations && $violations->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $violations->firstItem() }}–{{ $violations->lastItem() }} of {{ $violations->total() }} violations</span>
            {{ $violations->links() }}
        </div>
        @endif

        {{-- ===== INCIDENTS TABLE ===== --}}
        @elseif($tab === 'incidents')
        <div class="comp-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
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
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($inc->description, 55) }}</td>
                        <td class="table-cell">
                            @php $ics = match($inc->status) {'resolved'=>'badge-green','under_investigation'=>'badge-blue','for_validation'=>'badge-yellow','reported'=>'badge-yellow','closed'=>'badge-gray',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $ics }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                        </td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $inc->assignee->full_name ?? '—' }}</td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('incidents.edit', $inc->incident_id) }}" class="btn-icon btn-icon-edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="deleteIncident({{ $inc->incident_id }})" wire:confirm="Delete this incident?" class="btn-icon btn-icon-del"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="#FDB813" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No incidents found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or report a new incident.</div><a href="{{ route('incidents.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Report Incident</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($incidents && $incidents->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $incidents->firstItem() }}–{{ $incidents->lastItem() }} of {{ $incidents->total() }} incidents</span>
            {{ $incidents->links() }}
        </div>
        @endif

        @endif
    </div>
</div>
