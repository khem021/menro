@extends('layouts.app')
@section('title', 'Dashboard — MENRO')
@section('page-title', 'Dashboard')

@section('content')
<div style="display:flex;flex-direction:column;gap:0.75rem;">

    {{-- ── KPI Row ── --}}
    <div class="dash-kpi-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">

        <a href="{{ route('generators.index') }}" class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#34d399'" onmouseout="this.style.borderColor=''">
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Generators</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($totalGenerators) }}</div>
                <div style="font-size:0.6rem;color:var(--text-muted);">{{ $activeGenerators }} active &middot; {{ $nonCompliantCount }} non-compliant</div>
            </div>
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(52,211,153,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
        </a>

        <a href="{{ route('entries.index') }}" class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#fb923c'" onmouseout="this.style.borderColor=''">
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Waste This Month</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($thisMonthWaste, 1) }}<span style="font-size:0.75rem;color:var(--text-muted);font-weight:500;margin-left:2px;">kg</span></div>
                <div style="font-size:0.6rem;{{ $wasteTrendPct >= 0 ? 'color:#f87171;' : 'color:#34d399;' }}">
                    {{ $wasteTrendPct >= 0 ? '▲' : '▼' }} {{ abs($wasteTrendPct) }}% vs last month
                </div>
            </div>
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(251,146,60,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </a>

        <a href="{{ route('compliance.index') }}" class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#f87171'" onmouseout="this.style.borderColor=''">
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Open Violations</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($openViolations) }}</div>
                <div style="font-size:0.6rem;color:var(--danger);">{{ $criticalViolations }} high or critical severity</div>
            </div>
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(206,17,38,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </a>

        <a href="{{ route('compliance.index') }}" class="card" style="padding:0.625rem 0.875rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#60a5fa'" onmouseout="this.style.borderColor=''">
            <div>
                <div style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Active Incidents</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--text);line-height:1.1;margin:0.2rem 0;">{{ number_format($openIncidents) }}</div>
                <div style="font-size:0.6rem;color:var(--text-muted);">{{ $pendingCollections }} pending collections</div>
            </div>
            <div style="width:2rem;height:2rem;border-radius:0.5rem;background:rgba(96,165,250,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </a>

    </div>

    {{-- ── Alert Strip (only shown when there are actionable items) ── --}}
    @php $hasAlerts = $alerts['overdue_followups'] > 0 || $alerts['tomorrow_collections'] > 0 || $alerts['critical_violations'] > 0 || $alerts['for_inspection'] > 0; @endphp
    @if($hasAlerts)
    <div style="display:flex;gap:0.375rem;flex-wrap:wrap;flex-shrink:0;">
        @if($alerts['critical_violations'] > 0)
        <a href="{{ route('compliance.index') }}" style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.3rem 0.75rem;border-radius:999px;font-size:0.7rem;font-weight:600;background:rgba(248,113,113,0.12);border:1px solid rgba(248,113,113,0.3);color:#f87171;text-decoration:none;">
            <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $alerts['critical_violations'] }} critical violation{{ $alerts['critical_violations'] > 1 ? 's' : '' }} open → Review
        </a>
        @endif
        @if($alerts['overdue_followups'] > 0)
        <a href="{{ route('compliance.index') }}" style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.3rem 0.75rem;border-radius:999px;font-size:0.7rem;font-weight:600;background:rgba(253,184,19,0.1);border:1px solid rgba(253,184,19,0.3);color:#FDB813;text-decoration:none;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $alerts['overdue_followups'] }} overdue follow-up{{ $alerts['overdue_followups'] > 1 ? 's' : '' }} → Inspect
        </a>
        @endif
        @if($alerts['for_inspection'] > 0)
        <a href="{{ route('generators.index') }}" style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.3rem 0.75rem;border-radius:999px;font-size:0.7rem;font-weight:600;background:rgba(96,165,250,0.1);border:1px solid rgba(96,165,250,0.25);color:#60a5fa;text-decoration:none;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            {{ $alerts['for_inspection'] }} generator{{ $alerts['for_inspection'] > 1 ? 's' : '' }} due for inspection
        </a>
        @endif
        @if($alerts['tomorrow_collections'] > 0)
        <a href="{{ route('collections.index') }}" style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.3rem 0.75rem;border-radius:999px;font-size:0.7rem;font-weight:600;background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.25);color:#34d399;text-decoration:none;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            {{ $alerts['tomorrow_collections'] }} collection{{ $alerts['tomorrow_collections'] > 1 ? 's' : '' }} tomorrow → Prepare
        </a>
        @endif
    </div>
    @endif

    {{-- ── Main Content ── --}}
    <div class="dash-main-grid" style="display:grid;grid-template-columns:2fr 3fr;gap:0.75rem;align-items:start;">

        {{-- Left: Cluster Config --}}
        <div style="min-width:0;">
            @livewire('dashboard.cluster-config')
        </div>

        {{-- Right: Charts + Recent Entries --}}
        <div style="display:flex;flex-direction:column;gap:0.75rem;min-width:0;">

            {{-- Charts --}}
            <div class="dash-charts-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">

                <div class="card" style="padding:0.75rem;">
                    <div style="font-size:0.6875rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Waste by Cluster</div>
                    <div style="height:180px;position:relative;">
                        <canvas id="clusterChart" style="position:absolute;inset:0;width:100%!important;height:100%!important;"></canvas>
                    </div>
                </div>

                <div class="card" style="padding:0.75rem;">
                    <div style="font-size:0.6875rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Waste by Category</div>
                    <div style="height:180px;position:relative;">
                        <canvas id="categoryChart" style="position:absolute;inset:0;width:100%!important;height:100%!important;"></canvas>
                    </div>
                </div>

            </div>

            {{-- Recent Entries --}}
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="padding:0.5rem 0.875rem;border-bottom:1px solid var(--card-border);display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:0.6875rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Recent Waste Entries</div>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <a href="{{ route('entries.index') }}" style="font-size:0.6875rem;color:var(--accent);text-decoration:none;font-weight:500;">View all →</a>
                        <a href="{{ route('entries.create') }}" style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.25rem 0.625rem;font-size:0.6875rem;font-weight:600;border-radius:0.375rem;background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;text-decoration:none;">+ Add</a>
                    </div>
                </div>
                <div>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead style="position:sticky;top:0;background:var(--card-bg);">
                            <tr>
                                <th class="table-header">Date</th>
                                <th class="table-header">Generator</th>
                                <th class="table-header">Category</th>
                                <th class="table-header" style="text-align:right;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEntries->take(5) as $entry)
                            <tr class="table-row">
                                <td class="table-cell" style="white-space:nowrap;">{{ \Carbon\Carbon::parse($entry->entry_date)->format('M d') }}</td>
                                <td class="table-cell text-main" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $entry->wasteGenerator->generator_name ?? '—' }}</td>
                                <td class="table-cell">
                                    @php
                                        $cat = $entry->wasteCategory->category_name ?? '';
                                        $bc = match(true) {
                                            str_contains(strtolower($cat),'bio')   => 'badge-green',
                                            str_contains(strtolower($cat),'recycl')=> 'badge-blue',
                                            str_contains(strtolower($cat),'hazard')=> 'badge-red',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $bc }}" style="font-size:0.6rem;">{{ $cat ?: '—' }}</span>
                                </td>
                                <td class="table-cell" style="text-align:right;font-weight:600;color:var(--text);white-space:nowrap;">{{ number_format($entry->quantity, 1) }} kg</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="table-cell" style="text-align:center;padding:1rem;">No entries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid  = 'rgba(28,45,74,0.8)';
    const label = '#7b8fad';

    const clusterData = @json($clusterWaste);
    new Chart(document.getElementById('clusterChart'), {
        type: 'bar',
        data: {
            labels: clusterData.length ? clusterData.map(r => 'Cluster ' + r.cluster) : ['C1','C2','C3'],
            datasets: [{
                label: 'kg',
                data: clusterData.length ? clusterData.map(r => parseFloat(r.total)||0) : [0,0,0],
                backgroundColor: ['#FDB813','#60a5fa','#34d399'],
                borderRadius: 4, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: label, font: { size: 9 } } },
                y: { grid: { color: grid },    ticks: { color: label, font: { size: 9 } } },
            }
        }
    });

    const catData   = @json($categoryWaste);
    const catColors = ['#FDB813','#60a5fa','#34d399','#f87171','#a78bfa','#fb923c'];
    if (catData.length) {
        const catTotals = catData.map(r => parseFloat(r.total)||0);
        const catSum    = catTotals.reduce((a,b) => a+b, 0);
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catData.map(r => r.category_name),
                datasets: [{
                    data: catTotals,
                    backgroundColor: catColors,
                    hoverBackgroundColor: catColors.map(c => c + 'cc'),
                    borderWidth: 2,
                    borderColor: '#0a1628',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                layout: { padding: { top: 4, bottom: 4, left: 4, right: 4 } },
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            color: '#e8edf5',
                            font: { size: 8.5 },
                            padding: 8,
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            pointStyleWidth: 8,
                            generateLabels(chart) {
                                const data = chart.data;
                                return data.labels.map((lbl, i) => {
                                    const val = data.datasets[0].data[i];
                                    const pct = catSum > 0 ? ((val / catSum) * 100).toFixed(1) : 0;
                                    return {
                                        text: `${lbl}  ${pct}%`,
                                        fillStyle: catColors[i],
                                        strokeStyle: catColors[i],
                                        fontColor: '#ffffff',
                                        lineWidth: 0,
                                        hidden: false,
                                        index: i,
                                        pointStyle: 'circle',
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const val = ctx.parsed;
                                const pct = catSum > 0 ? ((val / catSum) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val.toLocaleString()} kg (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
