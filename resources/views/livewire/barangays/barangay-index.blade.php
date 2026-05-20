<div>
    @section('title', 'Barangay List — MENRO')
    @section('page-title', 'Barangay List')

    @if(session('success'))
    <div class="flash-success" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-error" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ session('error') }}</div>
    @endif

    <div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;overflow:hidden;">

        {{-- ── Stat strip + toolbar ── --}}
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-shrink:0;">
            <div class="card" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 1rem;min-width:150px;">
                <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(253,184,19,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" style="color:var(--accent)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Barangays</div>
                    <div style="font-size:1.25rem;font-weight:700;color:var(--accent);line-height:1.1;">{{ $barangays->count() }}</div>
                </div>
            </div>
            <div class="card" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 1rem;min-width:150px;">
                <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" style="color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </div>
                <div>
                    <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Total Sectors</div>
                    <div style="font-size:1.25rem;font-weight:700;color:#60a5fa;line-height:1.1;">{{ $totalSectors }}</div>
                </div>
            </div>
            <div class="card" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 1rem;min-width:150px;">
                <div style="width:1.875rem;height:1.875rem;border-radius:0.5rem;background:rgba(52,211,153,0.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Municipality</div>
                    <div style="font-size:0.8125rem;font-weight:700;color:#34d399;">Madrid, SDS</div>
                </div>
            </div>
            <div style="margin-left:auto;">
                <button wire:click="openAdd" class="btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Barangay
                </button>
            </div>
        </div>

        {{-- ── Two-panel layout ── --}}
        <div style="display:flex;gap:0.75rem;flex:1;min-height:0;overflow:hidden;">

            {{-- LEFT: Barangay list ── --}}
            <div class="card" style="width:256px;flex-shrink:0;display:flex;flex-direction:column;overflow:hidden;padding:0;">
                {{-- Search --}}
                <div style="padding:0.625rem;flex-shrink:0;border-bottom:1px solid var(--card-border);">
                    <div style="position:relative;">
                        <svg style="position:absolute;left:0.5rem;top:50%;transform:translateY(-50%);width:0.8125rem;height:0.8125rem;color:var(--text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input wire:model.debounce.300ms="search" type="text" placeholder="Search barangay…"
                               style="display:block;width:100%;padding:0.375rem 0.5rem 0.375rem 1.75rem;font-size:0.75rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    </div>
                </div>
                {{-- List --}}
                <div style="flex:1;min-height:0;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
                    @forelse($barangays as $i => $brgy)
                    <div wire:click="select({{ $brgy->barangay_id }})"
                         style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;cursor:pointer;transition:background .12s;
                                border-bottom:1px solid var(--card-border);
                                {{ $selectedId === $brgy->barangay_id
                                    ? 'background:var(--accent-glow);border-left:3px solid var(--accent);padding-left:calc(0.875rem - 3px);'
                                    : 'border-left:3px solid transparent;' }}">
                        <span style="width:1.375rem;height:1.375rem;border-radius:50%;font-size:0.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                     {{ $selectedId === $brgy->barangay_id ? 'background:var(--accent);color:#071020;' : 'background:var(--card-border);color:var(--text-muted);' }}">
                            {{ $i + 1 }}
                        </span>
                        <span style="font-size:0.8125rem;font-weight:500;flex:1;color:{{ $selectedId === $brgy->barangay_id ? 'var(--highlight)' : 'var(--text)' }};">
                            {{ $brgy->barangay_name }}
                        </span>
                        <span style="font-size:0.6rem;font-weight:700;padding:0.1rem 0.375rem;border-radius:999px;background:var(--card-border);color:var(--text-dim);">
                            {{ $brgy->sectors->count() }}
                        </span>
                    </div>
                    @empty
                    <div style="padding:2rem 1rem;text-align:center;font-size:0.75rem;color:var(--text-dim);">No barangays found.</div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: Detail panel ── --}}
            <div class="card" style="flex:1;min-height:0;display:flex;flex-direction:column;overflow:hidden;padding:0;">
                @if($selected)
                    {{-- Header --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem 1.25rem;flex-shrink:0;border-bottom:1px solid var(--card-border);">
                        <div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <svg width="14" height="14" style="color:var(--accent);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span style="font-size:0.9375rem;font-weight:700;color:var(--text);">Barangay {{ $selected->barangay_name }}</span>
                            </div>
                            <p style="font-size:0.6875rem;color:var(--text-muted);margin-top:2px;margin-left:1.375rem;">{{ $selected->municipality }}, {{ $selected->province }} &mdash; {{ $selected->sectors->count() }} sectors</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <button wire:click="openEdit({{ $selected->barangay_id }})" class="btn-ghost" style="font-size:0.75rem;padding:0.375rem 0.625rem;gap:0.375rem;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Rename
                            </button>
                            <button wire:click="deleteBrgy({{ $selected->barangay_id }})"
                                    wire:confirm="Delete Barangay {{ $selected->barangay_name }}? This will also remove all its sectors."
                                    class="btn-danger" style="font-size:0.75rem;padding:0.375rem 0.75rem;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>

                    {{-- Sectors grid --}}
                    <div style="flex:1;min-height:0;overflow-y:auto;padding:1rem 1.25rem;scrollbar-width:none;-ms-overflow-style:none;">
                        <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:0.75rem;">Sectors / Puroks</div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;">
                            @foreach($selected->sectors as $sector)
                            <div style="background:var(--bg);border:1px solid var(--card-border);border-radius:0.75rem;overflow:hidden;transition:border-color .15s;"
                                 onmouseover="this.style.borderColor='rgba(253,184,19,0.35)'" onmouseout="this.style.borderColor='var(--card-border)'">

                                {{-- Sector card header --}}
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.625rem 0.75rem;border-bottom:1px solid var(--card-border);">
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <div style="width:1.875rem;height:1.875rem;border-radius:50%;background:var(--accent-glow);border:1px solid rgba(253,184,19,0.2);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--accent);flex-shrink:0;">
                                            {{ $sector->sector_number }}
                                        </div>
                                        <div>
                                            <div style="font-size:0.8125rem;font-weight:600;color:var(--text);line-height:1.2;">{{ $sector->sector_name }}</div>
                                            <div style="font-size:0.6rem;color:var(--text-dim);">Sector {{ $sector->sector_number }}</div>
                                        </div>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:0.25rem;">
                                        <button wire:click="openSectorMembers({{ $sector->sector_id }})"
                                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0.25rem;border-radius:0.375rem;display:flex;transition:all .15s;"
                                                onmouseover="this.style.color='var(--accent)';this.style.background='var(--accent-glow)'" onmouseout="this.style.color='var(--text-dim)';this.style.background='none'"
                                                title="View purok members">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </button>
                                        <button wire:click="openEditSector({{ $sector->sector_id }})"
                                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0.25rem;border-radius:0.375rem;display:flex;transition:all .15s;"
                                                onmouseover="this.style.color='var(--accent)';this.style.background='var(--accent-glow)'" onmouseout="this.style.color='var(--text-dim)';this.style.background='none'"
                                                title="Edit sector">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Sector detail rows --}}
                                <div style="padding:0.625rem 0.75rem;display:flex;flex-direction:column;gap:0.375rem;">

                                    {{-- Households --}}
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <svg width="11" height="11" style="color:var(--text-dim);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span style="font-size:0.6875rem;color:var(--text-muted);flex:1;">Households</span>
                                        <span style="font-size:0.6875rem;font-weight:600;color:{{ $sector->household_count ? 'var(--text)' : 'var(--text-dim)' }};">
                                            {{ $sector->household_count ? number_format($sector->household_count) : '—' }}
                                        </span>
                                    </div>

                                    {{-- Purok Leader --}}
                                    <div style="display:flex;flex-direction:column;gap:0.2rem;">
                                        <div style="display:flex;align-items:center;gap:0.5rem;">
                                            <svg width="11" height="11" style="color:var(--text-dim);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span style="font-size:0.6875rem;color:var(--text-muted);flex:1;">Leader</span>
                                            <span style="font-size:0.6875rem;font-weight:600;color:{{ $sector->purok_leader_name ? 'var(--text)' : 'var(--text-dim)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:8rem;">
                                                {{ $sector->purok_leader_name ?? '—' }}
                                            </span>
                                        </div>
                                        @if($sector->purok_leader_contact)
                                        <div style="padding-left:calc(11px + 0.5rem);display:flex;justify-content:flex-end;">
                                            <span style="font-size:0.6rem;color:var(--text-dim);">{{ $sector->purok_leader_contact }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Waste estimate --}}
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <svg width="11" height="11" style="color:var(--text-dim);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                        <span style="font-size:0.6875rem;color:var(--text-muted);flex:1;">Waste</span>
                                        @if($sector->estimated_daily_waste_kg)
                                            <span style="font-size:0.6875rem;font-weight:600;color:#34d399;">{{ number_format($sector->estimated_daily_waste_kg, 1) }} kg</span>
                                            @php
                                                $freqLabel = match($sector->waste_frequency ?? '') {
                                                    'weekly'  => '/wk',
                                                    'monthly' => '/mo',
                                                    default   => '/day',
                                                };
                                            @endphp
                                            <span style="font-size:0.6rem;font-weight:700;color:#34d399;opacity:0.65;">{{ $freqLabel }}</span>
                                        @else
                                            <span style="font-size:0.6875rem;color:var(--text-dim);">—</span>
                                        @endif
                                    </div>

                                    {{-- Collection day --}}
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <svg width="11" height="11" style="color:var(--text-dim);flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span style="font-size:0.6875rem;color:var(--text-muted);flex:1;">Collection</span>
                                        @if($sector->collection_day)
                                        <span style="font-size:0.6rem;font-weight:700;padding:0.15rem 0.45rem;border-radius:999px;background:rgba(96,165,250,0.12);color:#60a5fa;">
                                            {{ $sector->collection_day }}
                                        </span>
                                        @else
                                        <span style="font-size:0.6875rem;color:var(--text-dim);">—</span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                @else
                    {{-- Empty state --}}
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.875rem;padding:2rem;">
                        <div style="width:3.5rem;height:3.5rem;border-radius:1rem;background:var(--card-border);display:flex;align-items:center;justify-content:center;">
                            <svg width="28" height="28" style="color:var(--text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div style="text-align:center;">
                            <p style="font-size:0.875rem;font-weight:600;color:var(--text);">Select a barangay</p>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">Click any barangay on the left to view and edit its sectors.</p>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECTOR MEMBERS MODAL
    ══════════════════════════════════════════════ --}}
    @if($showMembersModal)
    <div wire:click.self="$set('showMembersModal',false)"
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);">
        <div style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:1rem;padding:1.5rem;width:100%;max-width:520px;max-height:80vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem;flex-shrink:0;">
                <div>
                    <h3 style="font-size:0.9375rem;font-weight:700;color:var(--text);">Purok Members</h3>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin-top:3px;">
                        {{ $viewingSectorName }} &mdash; Sector {{ $viewingSectorNumber }}, Brgy. {{ $viewingSectorBarangay }}
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <button wire:click="$toggle('showAddMemberForm')"
                            style="display:flex;align-items:center;gap:0.375rem;font-size:0.75rem;font-weight:600;padding:0.375rem 0.625rem;border-radius:0.5rem;cursor:pointer;transition:all .15s;border:1px solid rgba(253,184,19,0.3);background:var(--accent-glow);color:var(--accent);"
                            onmouseover="this.style.background='rgba(253,184,19,0.2)'" onmouseout="this.style.background='var(--accent-glow)'">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add Member
                    </button>
                    <button wire:click="$set('showMembersModal',false)" style="background:none;border:none;cursor:pointer;color:var(--text-dim);display:flex;padding:0.25rem;border-radius:0.375rem;transition:color .15s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-dim)'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Add member form --}}
            @if($showAddMemberForm)
            <div style="flex-shrink:0;margin-bottom:1rem;padding:0.875rem;border-radius:0.625rem;background:#0b1425;border:1px solid var(--card-border);">
                <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);margin-bottom:0.625rem;">New Member</p>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <input wire:model.defer="member_name" type="text" placeholder="Full Name *"
                           style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:var(--card-bg);border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    @error('member_name')<p style="font-size:0.7rem;color:var(--danger);margin-top:-0.25rem;">{{ $message }}</p>@enderror
                    <input wire:model.defer="member_address" type="text" placeholder="Address (optional)"
                           style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:var(--card-bg);border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    <input wire:model.defer="member_contact" type="text" placeholder="Contact # (optional)"
                           style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:var(--card-bg);border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    <div style="display:flex;justify-content:flex-end;gap:0.375rem;margin-top:0.25rem;">
                        <button wire:click="$set('showAddMemberForm',false)" class="btn-secondary" style="font-size:0.75rem;padding:0.375rem 0.625rem;">Cancel</button>
                        <button wire:click="saveNewMember" class="btn-primary" style="font-size:0.75rem;padding:0.375rem 0.625rem;" wire:loading.attr="disabled">
                            <svg wire:loading.remove width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <svg wire:loading width="12" height="12" style="animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Save
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Members list --}}
            <div style="flex:1;overflow-y:auto;min-height:0;">
                @if(count($sectorMembers) === 0)
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;gap:0.5rem;">
                    <svg width="32" height="32" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p style="font-size:0.8125rem;color:var(--text-muted);">No members yet. Click <strong style="color:var(--accent)">Add Member</strong> to get started.</p>
                </div>
                @else
                <div style="display:flex;flex-direction:column;gap:0.375rem;">
                    @foreach($sectorMembers as $m)
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 0.75rem;border-radius:0.5rem;background:#0b1425;border:1px solid var(--card-border);">
                        <div style="width:2rem;height:2rem;border-radius:50%;background:var(--accent-glow);border:1px solid rgba(253,184,19,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.6875rem;font-weight:700;color:var(--accent);">
                            {{ strtoupper(substr($m['full_name'], 0, 1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.8125rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $m['full_name'] }}</div>
                            @if($m['address'] || $m['contact_number'])
                            <div style="font-size:0.6875rem;color:var(--text-muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ implode(' · ', array_filter([$m['address'], $m['contact_number']])) }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="deleteMember({{ $m['member_id'] }})"
                                wire:confirm="Remove {{ $m['full_name'] }} from this sector?"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0.25rem;border-radius:0.375rem;display:flex;flex-shrink:0;transition:all .15s;"
                                onmouseover="this.style.color='var(--danger)';this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.color='var(--text-dim)';this.style.background='none'">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div style="flex-shrink:0;padding-top:0.875rem;margin-top:0.875rem;border-top:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:0.75rem;color:var(--text-muted);">{{ count($sectorMembers) }} member{{ count($sectorMembers) !== 1 ? 's' : '' }}</span>
                <button wire:click="$set('showMembersModal',false)" class="btn-secondary" style="font-size:0.75rem;padding:0.375rem 0.75rem;">Close</button>
            </div>

        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         BARANGAY ADD / EDIT MODAL
    ══════════════════════════════════════════════ --}}
    @if($showBrgyModal)
    <div wire:click.self="$set('showBrgyModal',false)"
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);">
        <div style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:1rem;padding:1.5rem;width:100%;max-width:400px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h3 style="font-size:0.9375rem;font-weight:700;color:var(--text);">{{ $editingBrgyId ? 'Rename Barangay' : 'Add Barangay' }}</h3>
                <button wire:click="$set('showBrgyModal',false)" style="background:none;border:none;cursor:pointer;color:var(--text-dim);display:flex;padding:0.25rem;border-radius:0.375rem;transition:color .15s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-dim)'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.375rem;">Barangay Name <span style="color:var(--danger)">*</span></label>
                <input wire:model.defer="brgy_name" type="text" placeholder="e.g. San Antonio" wire:keydown.enter="saveBrgy"
                       style="display:block;width:100%;padding:0.5625rem 0.875rem;font-size:0.875rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                       onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                @error('brgy_name')<p style="margin-top:0.375rem;font-size:0.75rem;color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            @if(!$editingBrgyId)
            <div style="margin-bottom:1.25rem;padding:0.625rem 0.75rem;border-radius:0.5rem;background:var(--bg);border:1px solid var(--card-border);font-size:0.75rem;color:var(--text-muted);display:flex;align-items:center;gap:0.5rem;">
                <svg width="14" height="14" style="color:var(--accent);flex-shrink:0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                6 default sectors will be created automatically.
            </div>
            @endif
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding-top:1rem;border-top:1px solid var(--card-border);">
                <button wire:click="$set('showBrgyModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="saveBrgy" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading width="14" height="14" style="animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $editingBrgyId ? 'Update' : 'Add Barangay' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════
         SECTOR EDIT MODAL
    ══════════════════════════════════════════════ --}}
    @if($showSectorModal)
    @php
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    @endphp
    <div wire:click.self="$set('showSectorModal',false)"
         style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);">
        <div style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:1rem;padding:1.5rem;width:100%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem;">
                <div>
                    <h3 style="font-size:0.9375rem;font-weight:700;color:var(--text);">Edit Sector</h3>
                    @if($editingSectorNumber)
                    <p style="font-size:0.75rem;color:var(--text-muted);margin-top:3px;">
                        Sector {{ $editingSectorNumber }} &mdash; Brgy. {{ $editingSectorBarangay }}
                    </p>
                    @endif
                </div>
                <button wire:click="$set('showSectorModal',false)" style="background:none;border:none;cursor:pointer;color:var(--text-dim);display:flex;padding:0.25rem;border-radius:0.375rem;transition:color .15s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-dim)'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Fields --}}
            <div style="display:flex;flex-direction:column;gap:0.75rem;">

                {{-- Sector Name --}}
                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">
                        Sector / Purok Name <span style="color:var(--danger)">*</span>
                    </label>
                    <input wire:model.defer="sector_name" type="text" placeholder="e.g. Purok Maligaya"
                           style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    @error('sector_name')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                </div>

                {{-- Row: Households + Waste (kg) + Frequency --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.625rem;">
                    <div>
                        <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Households</label>
                        <input wire:model.defer="sector_household_count" type="number" min="0" max="9999" placeholder="e.g. 120"
                               style="display:block;width:100%;padding:0.5rem 0.625rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                        @error('sector_household_count')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Waste (kg)</label>
                        <input wire:model.defer="sector_daily_waste" type="number" min="0" step="0.1" placeholder="e.g. 45.5"
                               style="display:block;width:100%;padding:0.5rem 0.625rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                        @error('sector_daily_waste')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Frequency</label>
                        <select wire:model.defer="sector_waste_frequency"
                                style="display:block;width:100%;padding:0.5rem 0.625rem;font-size:0.8125rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;cursor:pointer;"
                                onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'">
                            <option value="">— Not set —</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        @error('sector_waste_frequency')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Purok Leader Name --}}
                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Purok Leader Name</label>
                    <input wire:model.defer="sector_leader_name" type="text" placeholder="e.g. Juan Dela Cruz"
                           style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                    @error('sector_leader_name')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                </div>

                {{-- Row: Contact + Collection Day --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.625rem;">
                    <div>
                        <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Leader Contact #</label>
                        <input wire:model.defer="sector_leader_contact" type="text" placeholder="e.g. 09171234567"
                               style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'" />
                        @error('sector_leader_contact')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#93afd4;margin-bottom:0.3rem;">Collection Day</label>
                        <select wire:model.defer="sector_collection_day"
                                style="display:block;width:100%;padding:0.5rem 0.75rem;font-size:0.875rem;font-family:inherit;color:var(--text);background:#0b1425;border:1px solid var(--card-border);border-radius:0.5rem;outline:none;transition:border-color .15s;cursor:pointer;"
                                onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--card-border)'">
                            <option value="">— Not set —</option>
                            @foreach($days as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('sector_collection_day')<p style="margin-top:0.25rem;font-size:0.7rem;color:var(--danger)">{{ $message }}</p>@enderror
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding-top:1rem;margin-top:0.25rem;border-top:1px solid var(--card-border);">
                <button wire:click="$set('showSectorModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="saveSector" class="btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading.remove width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading width="14" height="14" style="animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Save Sector
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
