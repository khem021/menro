<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">
    @section('title', 'Waste Entries — MENRO')
    @section('page-title', 'Waste Entries')

    {{-- flash handled by global toast --}}

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;flex-shrink:0;">
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total Records</div><div style="font-size:1.375rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($stats['total']) }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:#1c2d4a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">This Month</div><div style="font-size:1.375rem;font-weight:700;color:#60a5fa;line-height:1.1;margin:0.2rem 0;">{{ $stats['month_entries'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Monthly Volume</div><div style="font-size:1.375rem;font-weight:700;color:#34d399;line-height:1.1;margin:0.2rem 0;">{{ number_format($stats['month_volume'],1) }}<span style="font-size:0.7rem;color:var(--text-muted);font-weight:500;margin-left:2px;">kg</span></div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg></div>
        </div>
        <div class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
            <div><div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Today</div><div style="font-size:1.375rem;font-weight:700;color:#FDB813;line-height:1.1;margin:0.2rem 0;">{{ $stats['today_entries'] }}</div></div>
            <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(253,184,19,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="13" height="13" fill="none" stroke="#FDB813" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:0.625rem 0.875rem;flex-shrink:0;">
        <div style="display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Search</span>
                <div style="position:relative;">
                    <svg style="position:absolute;left:0.5rem;top:50%;transform:translateY(-50%);width:0.875rem;height:0.875rem;color:var(--text-muted);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Generator name…" class="form-input" style="padding-left:1.875rem;width:13rem;" />
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Generator</span>
                <select wire:model="generator_id" class="form-select" style="width:12rem;"><option value="">All Generators</option>@foreach($generators as $g)<option value="{{ $g->generator_id }}">{{ $g->generator_name }}</option>@endforeach</select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">Category</span>
                <select wire:model="category_id" class="form-select" style="width:10rem;"><option value="">All Categories</option>@foreach($categories as $c)<option value="{{ $c->category_id }}">{{ $c->category_name }}</option>@endforeach</select>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">From</span>
                <input type="date" wire:model="date_from" class="form-input" style="width:9rem;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);">To</span>
                <input type="date" wire:model="date_to" class="form-input" style="width:9rem;" />
            </div>
            @if($search || $generator_id || $category_id || $date_from || $date_to)
            <button wire:click="$set('search','');$set('generator_id','');$set('category_id','');$set('date_from','');$set('date_to','')" class="btn-ghost" style="font-size:0.75rem;">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Clear
            </button>
            @endif
            <a href="{{ route('entries.create') }}" class="btn-primary" style="margin-left:auto;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Entry</a>
        </div>
    </div>

    {{-- Table --}}
    <style>.ent-tbl::-webkit-scrollbar{display:none}</style>
    <div class="card tbl-card" style="flex:1;min-height:0;display:flex;flex-direction:column;padding:0;overflow:hidden;">
        <div class="ent-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Date</th>
                        <th class="table-header">Generator</th>
                        <th class="table-header">Category</th>
                        <th class="table-header" style="text-align:right;">Quantity</th>
                        <th class="table-header">Recorded By</th>
                        <th class="table-header" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                    <tr class="table-row">
                        <td class="table-cell" style="white-space:nowrap;">
                            <div style="font-weight:500;color:var(--text);font-size:0.8125rem;">{{ \Carbon\Carbon::parse($entry->entry_date)->format('M d, Y') }}</div>
                            <div style="font-size:0.6875rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($entry->entry_date)->diffForHumans() }}</div>
                        </td>
                        <td class="table-cell" style="font-weight:600;color:var(--text);font-size:0.8125rem;">{{ $entry->wasteGenerator->generator_name ?? '—' }}</td>
                        <td class="table-cell">
                            @php $cat = $entry->wasteCategory->category_name ?? '';
                            $cc = match(true) {
                                str_contains(strtolower($cat),'bio')    => 'badge-green',
                                str_contains(strtolower($cat),'recycl') => 'badge-blue',
                                str_contains(strtolower($cat),'hazard') => 'badge-red',
                                default => 'badge-gray'
                            }; @endphp
                            <span class="badge {{ $cc }}">{{ $cat ?: '—' }}</span>
                        </td>
                        <td class="table-cell" style="text-align:right;font-weight:600;color:var(--text);white-space:nowrap;">{{ number_format($entry->quantity, 2) }} <span style="font-size:0.6875rem;color:var(--text-muted);font-weight:400;">{{ $entry->unit }}</span></td>
                        <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $entry->encodedBy->full_name ?? '—' }}</td>
                        <td class="table-cell" style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.25rem;">
                                <a href="{{ route('entries.edit', $entry->entry_id) }}" class="btn-icon btn-icon-edit" title="Edit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <button wire:click="delete({{ $entry->entry_id }})" wire:confirm="Delete this entry?" class="btn-icon btn-icon-del" title="Delete"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:3rem 1rem;text-align:center;"><div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;"><div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg></div><div style="font-size:0.875rem;font-weight:600;color:var(--text);">No entries found</div><div style="font-size:0.75rem;color:var(--text-muted);">Adjust filters or record a new waste entry.</div><a href="{{ route('entries.create') }}" class="btn-primary" style="font-size:0.75rem;margin-top:0.25rem;">Add Entry</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
        <div style="flex-shrink:0;padding:0.5rem 0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }} entries</span>
            {{ $entries->links() }}
        </div>
        @endif
    </div>
</div>
