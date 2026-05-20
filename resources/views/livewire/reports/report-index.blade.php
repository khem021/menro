<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;max-width:720px;margin:0 auto;">
    @section('title', 'Reports — MENRO')
    @section('page-title', 'Reports')

    {{-- ── Header ── --}}
    <div style="margin-bottom:1rem;flex-shrink:0;">
        <h2 style="font-size:1.125rem;font-weight:700;color:var(--text);margin:0;">Export Reports</h2>
        <p style="font-size:0.8125rem;color:var(--text-muted);margin:0.25rem 0 0;">Generate and download Excel reports for any date range</p>
    </div>

    {{-- ── Report Type Cards ── --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem;margin-bottom:0.75rem;flex-shrink:0;">
        @foreach([
            ['monthly_waste',      'Monthly Waste Report',  'Waste entries grouped by month',         '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
            ['compliance_summary', 'Compliance Summary',    'Generator compliance status overview',   '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
            ['incident_summary',   'Incident Summary',      'Environmental incidents log',            '<path d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
            ['collection_summary', 'Collection Summary',    'Waste collection schedule & results',    '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        ] as [$val, $label, $sub, $icon])
        @php $active = $report_type === $val; @endphp
        <label style="cursor:pointer;display:block;">
            <input type="radio" wire:model="report_type" value="{{ $val }}" style="display:none;">
            <div style="
                padding:0.625rem 0.875rem;
                border-radius:0.625rem;
                border:1px solid {{ $active ? '#FDB813' : 'var(--card-border)' }};
                background:{{ $active ? 'rgba(253,184,19,0.07)' : 'var(--card-bg)' }};
                display:flex;align-items:center;gap:0.625rem;
                transition:border-color .15s,background .15s;
            ">
                <div style="
                    width:1.75rem;height:1.75rem;border-radius:0.375rem;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;
                    background:{{ $active ? 'rgba(253,184,19,0.15)' : '#1c2d4a' }};
                    color:{{ $active ? '#FDB813' : 'var(--text-muted)' }};
                ">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon !!}</svg>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.78rem;font-weight:600;color:{{ $active ? '#FDB813' : 'var(--text)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $label }}</div>
                    <div style="font-size:0.6rem;color:var(--text-muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $sub }}</div>
                </div>
            </div>
        </label>
        @endforeach
    </div>

    {{-- ── Period Presets ── --}}
    <div style="display:flex;gap:0.3rem;flex-wrap:wrap;margin-bottom:0.5rem;flex-shrink:0;">
        @foreach([
            ['today',      'Today'],
            ['yesterday',  'Yesterday'],
            ['this_week',  'This Week'],
            ['last_week',  'Last Week'],
            ['this_month', 'This Month'],
            ['last_month', 'Last Month'],
        ] as [$key, $label])
        <button wire:click="setPeriod('{{ $key }}')" class="btn-ghost" style="font-size:0.7rem;padding:0.2rem 0.6rem;border-radius:999px;">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ── Date Range + Export ── --}}
    <div class="card" style="padding:0.875rem;margin-bottom:0.75rem;flex-shrink:0;">
        <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:0.625rem;align-items:end;">
            <div>
                <label class="form-label">From Date</label>
                <input type="date" wire:model="date_from" class="form-input" />
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" wire:model="date_to" class="form-input" />
            </div>
            <a href="{{ $this->getExportUrl() }}"
               style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;
                      border-radius:0.5rem;font-size:0.8125rem;font-weight:600;
                      background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;
                      text-decoration:none;white-space:nowrap;transition:opacity .15s;"
               onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ $this->getPrintUrl() }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;
                      border-radius:0.5rem;font-size:0.8125rem;font-weight:600;
                      background:var(--card-bg);border:1px solid var(--card-border);color:var(--text);
                      text-decoration:none;white-space:nowrap;transition:opacity .15s;"
               onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print PDF
            </a>
        </div>
    </div>

    {{-- ── Recent Exports (fills remaining height) ── --}}
    <div class="card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;flex:1;min-height:0;">
        <div style="padding:0.75rem 1rem;border-bottom:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div class="card-title" style="margin-bottom:0;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Recent Exports
            </div>
            <span style="font-size:0.6875rem;color:var(--text-dim);">Last {{ $reports->count() }} exports</span>
        </div>

        {{-- Scrollable table body, no visible scrollbar --}}
        <div style="flex:1;min-height:0;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
            <style>#report-table-wrap::-webkit-scrollbar{display:none}</style>
            <div id="report-table-wrap" style="height:100%;overflow-y:auto;scrollbar-width:none;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                    <tr>
                        <th class="table-header">Type</th>
                        <th class="table-header">Generated</th>
                        <th class="table-header">By</th>
                        <th class="table-header">Remarks</th>
                        <th class="table-header" style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr class="table-row">
                        <td class="table-cell">
                            <span class="badge badge-blue">{{ ucwords(str_replace('_', ' ', $report->report_type)) }}</span>
                        </td>
                        <td class="table-cell" style="white-space:nowrap;">
                            <div style="color:var(--text);font-size:0.8125rem;font-weight:500;">{{ \Carbon\Carbon::parse($report->generated_at)->format('M d, Y') }}</div>
                            <div style="color:var(--text-muted);font-size:0.6875rem;">{{ \Carbon\Carbon::parse($report->generated_at)->format('h:i A') }}</div>
                        </td>
                        <td class="table-cell text-main" style="white-space:nowrap;">{{ $report->generatedBy->full_name ?? '—' }}</td>
                        <td class="table-cell" style="font-size:0.75rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $report->remarks ?? '—' }}</td>
                        <td class="table-cell" style="text-align:center;">
                            @if($report->file_path)
                                <span class="badge badge-green"><span class="badge-dot"></span> Exported</span>
                            @else
                                <span class="badge badge-gray">Logged</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:3rem 1rem;text-align:center;">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:0.625rem;">
                                <div style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1c2d4a;display:flex;align-items:center;justify-content:center;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:0.875rem;font-weight:600;color:var(--text);">No exports yet</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;">Select a report type and click Export Excel.</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
