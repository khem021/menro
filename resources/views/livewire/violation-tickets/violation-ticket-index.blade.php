<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Violation Tickets — MENRO')
    @section('page-title', 'Violation')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div class="mob-stats-grid" style="flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total Tickets</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($stats['total']) }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:var(--card-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Today</div><div style="font-size:1.375rem;font-weight:700;color:var(--accent-text);line-height:1.1;margin:0.2rem 0;">{{ $stats['today_count'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(253,184,19,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="var(--accent-text)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">This Month</div><div style="font-size:1.375rem;font-weight:700;color:var(--info-text);line-height:1.1;margin:0.2rem 0;">{{ $stats['month_count'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="var(--info-text)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total Penalties Assessed</div><div style="font-size:1.375rem;font-weight:700;color:var(--danger-text);line-height:1.1;margin:0.2rem 0;">₱{{ number_format($stats['total_penalties'], 2) }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(248,113,113,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="var(--danger-text)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5m0-6c1.11 0 2.08.402 2.599 1M12 8V6m0 8v2m0-10a9 9 0 100 18 9 9 0 000-18z"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;flex:2;min-width:180px;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Search</span>
                <div style="position:relative;">
                    <svg style="position:absolute;left:0.5rem;top:50%;transform:translateY(-50%);width:0.875rem;height:0.875rem;color:var(--text-muted);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Violator name or ticket #…" class="form-input" style="padding-left:1.875rem;" />
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Type of Violation</span>
                <select wire:model="violation_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach(\App\Models\ViolationTicket::VIOLATION_TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('violation-tickets.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Issue Ticket</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.vt-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="vt-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Ticket #</th>
                        <th class="table-header">Name of Violator</th>
                        <th class="table-header">Type of Violation</th>
                        <th class="table-header">Address</th>
                        <th class="table-header">Penalty</th>
                        <th class="table-header">Issued</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr class="table-row">
                        <td class="table-cell" style="font-weight:600;color:var(--text);font-size:0.8125rem;white-space:nowrap;">{{ $t->ticket_number }}</td>
                        <td class="table-cell" style="color:var(--text);font-size:0.8125rem;">{{ $t->violator_name }}</td>
                        <td class="table-cell">
                            @php $tc = match($t->violation_type) {'littering'=>'badge-yellow','dumping'=>'badge-red','burning'=>'badge-orange','no_segregation'=>'badge-blue',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $tc }}">{{ \App\Models\ViolationTicket::VIOLATION_TYPES[$t->violation_type] ?? $t->violation_type }}</span>
                            @if($t->violation_type === 'other' && $t->other_violation_description)
                                <div style="font-size:0.6875rem;color:var(--text-muted);margin-top:0.15rem;">{{ $t->other_violation_description }}</div>
                            @endif
                        </td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $t->address }}</td>
                        <td class="table-cell">
                            @php $oc = match($t->offense_number) {1=>'badge-blue',2=>'badge-yellow',default=>'badge-red'}; @endphp
                            <span class="badge {{ $oc }}">{{ \App\Models\ViolationTicket::offenseLabel($t->offense_number) }}</span>
                            <div style="font-size:0.75rem;color:var(--text);font-weight:600;margin-top:0.15rem;">₱{{ number_format($t->penalty_amount, 2) }}</div>
                        </td>
                        <td class="table-cell" style="white-space:nowrap;">
                            <div style="font-weight:500;color:var(--text);font-size:0.8125rem;">{{ \Carbon\Carbon::parse($t->issued_date)->format('M d, Y') }}</div>
                            <div style="font-size:0.6875rem;color:var(--text-muted);">{{ $t->issuedBy->full_name ?? '—' }}</div>
                        </td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('violation-tickets.edit', $t->ticket_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $t->ticket_id }})" wire:confirm="Delete this violation ticket?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:var(--card-border);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No violation tickets found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or issue a new ticket.</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets</span>
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
