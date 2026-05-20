@extends('layouts.app')

@section('title', 'Analytics — MENRO')
@section('page-title', 'Analytics')

@section('content')

<div style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;gap:0.5rem;overflow:hidden;">

    {{-- 2×2 Grid --}}
    <div style="flex:1;min-height:0;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:0.5rem;">

        {{-- Waste by Category --}}
        <div class="card" style="padding:0.75rem;display:flex;flex-direction:column;overflow:hidden;">
            <div class="card-title" style="flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Waste by Category (kg)
            </div>
            <div style="flex:1;min-height:0;position:relative;">
                <canvas id="categoryChart" style="position:absolute;inset:0;width:100%!important;height:100%!important;"></canvas>
            </div>
            @php $catPalette = ['#FDB813','#60a5fa','#34d399','#f87171','#a78bfa','#fb923c','#38bdf8','#f472b6']; @endphp
            <div style="flex-shrink:0;display:grid;grid-template-columns:1fr 1fr;gap:0.2rem 0.75rem;margin-top:0.5rem;">
                @foreach($wasteByCategory as $i => $row)
                @php $hex = $catPalette[$i % count($catPalette)]; @endphp
                <div style="display:flex;align-items:center;gap:0.375rem;">
                    <span style="width:7px;height:7px;border-radius:2px;flex-shrink:0;background:{{ $hex }};"></span>
                    <span style="font-size:0.6rem;color:var(--text-muted);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row->category_name }}</span>
                    <span style="font-size:0.6rem;font-weight:600;color:var(--text);font-variant-numeric:tabular-nums;">{{ number_format($row->total, 1) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Waste by Generator Type --}}
        <div class="card" style="padding:0.75rem;display:flex;flex-direction:column;overflow:hidden;">
            <div class="card-title" style="flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                Waste by Generator Type (kg)
            </div>
            <div style="flex:1;min-height:0;position:relative;">
                <canvas id="generatorTypeChart" style="position:absolute;inset:0;width:100%!important;height:100%!important;"></canvas>
            </div>
        </div>

        {{-- Waste by Cluster --}}
        <div class="card" style="padding:0.75rem;display:flex;flex-direction:column;overflow:hidden;">
            <div class="card-title" style="flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Waste by Cluster (kg)
            </div>
            <div style="flex:1;min-height:0;position:relative;">
                <canvas id="clusterChart" style="position:absolute;inset:0;width:100%!important;height:100%!important;"></canvas>
            </div>
        </div>

        {{-- Top 10 Generators --}}
        <div class="card" style="padding:0;display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:0.5rem 0.875rem;border-bottom:1px solid var(--card-border);flex-shrink:0;">
                <div class="card-title" style="margin-bottom:0;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Top 10 Generators by Volume
                </div>
            </div>
            <style>.analytics-gen-tbl::-webkit-scrollbar{display:none}</style>
            <div class="analytics-gen-tbl" style="flex:1;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="position:sticky;top:0;z-index:1;background:var(--card-bg);">
                        <tr>
                            <th class="table-header">#</th>
                            <th class="table-header">Generator</th>
                            <th class="table-header">Barangay</th>
                            <th class="table-header">Compliance</th>
                            <th class="table-header" style="text-align:right;">Total (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topGenerators as $i => $gen)
                        <tr class="table-row">
                            <td class="table-cell" style="font-family:monospace;font-size:0.75rem;color:var(--text-muted);">{{ $i + 1 }}</td>
                            <td class="table-cell text-main">{{ $gen->generator_name }}</td>
                            <td class="table-cell" style="color:var(--text-muted);font-size:0.8125rem;">{{ $gen->barangay_name ?? '—' }}</td>
                            <td class="table-cell">
                                @php $badgeClass = match($gen->compliance_status) {'compliant'=>'badge-green','for_inspection'=>'badge-yellow','non_compliant'=>'badge-red',default=>'badge-gray'}; @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $gen->compliance_status)) }}</span>
                            </td>
                            <td class="table-cell" style="text-align:right;font-weight:600;color:var(--text);">{{ number_format($gen->total, 2) }}</td>
                        </tr>
                        @endforeach
                        @if(count($topGenerators) === 0)
                        <tr><td colspan="5" style="padding:3rem 1rem;text-align:center;color:var(--text-muted);font-size:0.8125rem;">No data available for the last 12 months.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const gridColor  = 'rgba(28,45,74,0.8)';
    const labelColor = '#7b8fad';
    const palette    = ['#FDB813','#60a5fa','#34d399','#f87171','#a78bfa','#fb923c','#38bdf8','#f472b6'];

    const catData = @json($wasteByCategory);
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: catData.map(r => r.category_name),
            datasets: [{
                label: 'kg',
                data: catData.map(r => parseFloat(r.total) || 0),
                backgroundColor: catData.map((_, i) => palette[i % palette.length]),
                borderRadius: 3, borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 9 } } },
                y: { grid: { display: false }, ticks: { color: labelColor, font: { size: 9 } } },
            }
        }
    });

    const gtData = @json($wasteByGeneratorType);
    new Chart(document.getElementById('generatorTypeChart'), {
        type: 'doughnut',
        data: {
            labels: gtData.map(r => r.type_name),
            datasets: [{ data: gtData.map(r => parseFloat(r.total) || 0), backgroundColor: palette, borderWidth: 2, borderColor: '#0f1d35' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { color: labelColor, font: { size: 9 }, padding: 8, boxWidth: 9 } } }
        }
    });

    const clData   = @json($wasteByCluster);
    const clColors = { 1: '#FDB813', 2: '#34d399', 3: '#60a5fa' };
    new Chart(document.getElementById('clusterChart'), {
        type: 'bar',
        data: {
            labels: clData.map(r => 'Cluster ' + (r.cluster || '?')),
            datasets: [{
                label: 'kg',
                data: clData.map(r => parseFloat(r.total) || 0),
                backgroundColor: clData.map(r => clColors[r.cluster] ?? '#7b8fad'),
                borderRadius: 4, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 9 } } },
                y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 9 } } },
            }
        }
    });
});
</script>
@endpush
