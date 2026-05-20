<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Generators — MENRO')
    @section('page-title', 'Waste Generators')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ $stats['total'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:#1c2d4a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Active</div><div style="font-size:1.375rem;font-weight:700;color:#34d399;line-height:1.1;margin:0.2rem 0;">{{ $stats['active'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Compliant</div><div style="font-size:1.375rem;font-weight:700;color:#60a5fa;line-height:1.1;margin:0.2rem 0;">{{ $stats['compliant'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Non-Compliant</div><div style="font-size:1.375rem;font-weight:700;color:#f87171;line-height:1.1;margin:0.2rem 0;">{{ $stats['non_compliant'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(248,113,113,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;flex:1;min-width:160px;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Search</span>
                <div style="position:relative;"><svg style="position:absolute;left:0.625rem;top:50%;transform:translateY(-50%);color:var(--text-dim);" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input wire:model.debounce.400ms="search" type="text" placeholder="Name, contact, address…" class="form-input" style="padding-left:2rem;" /></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Status</span>
                <select wire:model="status" class="form-select" style="width:9rem;"><option value="">All Statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Compliance</span>
                <select wire:model="compliance_status" class="form-select" style="width:11rem;"><option value="">All</option><option value="compliant">Compliant</option><option value="for_inspection">For Inspection</option><option value="non_compliant">Non-Compliant</option></select>
            </div>
            @if($search || $status || $compliance_status)
            <button wire:click="$set('search','');$set('status','');$set('compliance_status','')" class="btn-ghost" style="font-size:0.75rem;">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Clear
            </button>
            @endif
            <a href="{{ route('generators.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Generator</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.gen-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card tbl-card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="gen-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Generator</th>
                        <th class="table-header">Type</th>
                        <th class="table-header">Barangay</th>
                        <th class="table-header">Compliance</th>
                        <th class="table-header">Status</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($generators as $gen)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div style="display:flex;align-items:center;gap:0.625rem;">
                                <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.15);display:flex;align-items:center;justify-content:center;font-size:0.6875rem;font-weight:700;color:#34d399;flex-shrink:0;">{{ strtoupper(substr($gen->generator_name,0,1)) }}</div>
                                <div><div style="font-weight:600;color:var(--text);font-size:0.8125rem;">{{ $gen->generator_name }}</div>
                                @if($gen->contact_person)<div style="font-size:0.6875rem;color:var(--text-muted);">{{ $gen->contact_person }}</div>@endif</div>
                            </div>
                        </td>
                        <td class="table-cell">{{ $gen->generatorType->type_name ?? '—' }}</td>
                        <td class="table-cell">{{ $gen->barangay->barangay_name ?? '—' }}</td>
                        <td class="table-cell">
                            @php $cs = match($gen->compliance_status) {'compliant'=>'badge-green','for_inspection'=>'badge-yellow','non_compliant'=>'badge-red',default=>'badge-gray'}; @endphp
                            <span class="badge {{ $cs }}">{{ ucfirst(str_replace('_',' ',$gen->compliance_status)) }}</span>
                        </td>
                        <td class="table-cell"><span class="badge {{ $gen->status==='active'?'badge-green':'badge-gray' }}">{{ ucfirst($gen->status) }}</span></td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('generators.edit', $gen->generator_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $gen->generator_id }})" wire:confirm="Delete this generator?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No generators found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or add a new generator.</div><a href="{{ route('generators.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Add Generator</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($generators->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $generators->firstItem() }}–{{ $generators->lastItem() }} of {{ $generators->total() }} generators</span>
            {{ $generators->links() }}
        </div>
        @endif
    </div>
</div>
