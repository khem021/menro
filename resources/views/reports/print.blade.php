<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }} — MENRO</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:10pt; color:#1a1a2e; background:#fff; }

    /* ── Header ── */
    .rpt-header { background:#071020; color:#FDB813; padding:1rem 1.5rem 0.75rem; }
    .rpt-header-top { display:flex; align-items:center; justify-content:space-between; }
    .rpt-org { font-size:7pt; color:#7b8fad; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.15rem; }
    .rpt-title { font-size:16pt; font-weight:700; letter-spacing:0.02em; }
    .rpt-meta { margin-top:0.5rem; font-size:8pt; color:#7b8fad; display:flex; gap:1.5rem; flex-wrap:wrap; border-top:1px solid #1c2d4a; padding-top:0.5rem; }
    .rpt-meta span { display:flex; align-items:center; gap:0.3rem; }

    /* ── Table ── */
    .rpt-table-wrap { padding:1rem 1.5rem; }
    table { width:100%; border-collapse:collapse; font-size:9pt; }
    thead th {
        background:#0f1d35; color:#e8edf5; font-weight:700; font-size:8pt;
        text-transform:uppercase; letter-spacing:0.06em;
        padding:0.45rem 0.625rem; text-align:left;
        border-bottom:2px solid #FDB813;
    }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody tr:nth-child(odd)  { background:#ffffff; }
    tbody td { padding:0.35rem 0.625rem; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
    tbody tr:hover { background:#fef9ec; }

    /* ── Status/category cells ── */
    .cell-green  { background:#dcfce7; color:#14532d; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }
    .cell-red    { background:#fee2e2; color:#7f1d1d; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }
    .cell-amber  { background:#fef3c7; color:#78350f; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }
    .cell-blue   { background:#dbeafe; color:#1e3a5f; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }
    .cell-purple { background:#ede9fe; color:#3b0764; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }
    .cell-gray   { background:#f1f5f9; color:#475569; font-weight:600; border-radius:3px; padding:0.15rem 0.4rem; font-size:8pt; }

    /* ── Footer ── */
    .rpt-footer {
        margin-top:1.5rem; padding:0.625rem 1.5rem;
        border-top:1px solid #e2e8f0; font-size:8pt; color:#94a3b8;
        display:flex; justify-content:space-between; align-items:center;
    }

    /* ── Empty state ── */
    .rpt-empty { padding:2rem; text-align:center; color:#94a3b8; font-size:10pt; }

    /* ── Print toolbar (hidden when printing) ── */
    .print-bar {
        position:fixed; top:0; left:0; right:0; z-index:999;
        background:#1e293b; color:#e2e8f0; padding:0.5rem 1.5rem;
        display:flex; align-items:center; justify-content:space-between;
        font-size:9pt; box-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .print-bar button {
        background:#FDB813; color:#071020; border:none; padding:0.35rem 1rem;
        border-radius:0.375rem; font-weight:700; font-size:9pt; cursor:pointer;
    }
    .print-bar a { color:#94a3b8; text-decoration:none; font-size:8.5pt; }
    .print-bar a:hover { color:#e2e8f0; }

    @media print {
        .print-bar { display:none !important; }
        body { font-size:9pt; }
        .rpt-table-wrap { padding:0.5rem 0.75rem; }
        thead th { font-size:7.5pt; padding:0.3rem 0.4rem; }
        tbody td { padding:0.25rem 0.4rem; font-size:8.5pt; }
        @page { margin:1cm; size:A4 landscape; }
    }
</style>
</head>
<body>

{{-- ── Print toolbar ── --}}
<div class="print-bar">
    <span>📄 {{ $title }}</span>
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="{{ route('reports.index') }}">← Back to Reports</a>
        <button onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
</div>

{{-- ── Push content below fixed bar ── --}}
<div style="height:2.5rem;" class="print-bar-spacer"></div>

{{-- ── Report header ── --}}
<div class="rpt-header">
    <div class="rpt-header-top">
        <div>
            <div class="rpt-org">Republic of the Philippines &middot; MENRO &middot; Municipality of Madrid, Surigao del Sur</div>
            <div class="rpt-title">{{ strtoupper($title) }}</div>
        </div>
    </div>
    <div class="rpt-meta">
        <span>📅 Period: <strong style="color:#e8edf5;">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</strong></span>
        <span>🕐 Generated: <strong style="color:#e8edf5;">{{ now()->format('M d, Y h:i A') }}</strong></span>
        <span>📊 Records: <strong style="color:#e8edf5;">{{ count($rows) }}</strong></span>
    </div>
</div>

{{-- ── Table ── --}}
<div class="rpt-table-wrap">
@if(count($rows) > 0)
<table>
    <thead>
        <tr>
            @foreach($headers as $h)
            <th>{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            @foreach($row as $colIdx => $cell)
            <td>
                @if($colIdx === $colorKeyCol && $cell)
                    @php
                        $k   = strtolower(trim((string) $cell));
                        $cls = match(true) {
                            str_contains($k,'bio')                              => 'cell-green',
                            str_contains($k,'recycl') && !str_contains($k,'non')=> 'cell-blue',
                            str_contains($k,'residual') || str_contains($k,'non-recycl') || str_contains($k,'non_recycl') => 'cell-red',
                            str_contains($k,'hazard') || str_contains($k,'special') => 'cell-amber',
                            str_contains($k,'mixed')                            => 'cell-purple',
                            in_array($k,['compliant','completed','resolved','closed']) => 'cell-green',
                            in_array($k,['non compliant','non_compliant','open','missed']) => 'cell-red',
                            in_array($k,['for inspection','for_inspection','pending','ongoing','in progress']) => 'cell-amber',
                            default => 'cell-gray',
                        };
                    @endphp
                    <span class="{{ $cls }}">{{ $cell }}</span>
                @else
                    {{ $cell ?? '—' }}
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="rpt-empty">No records found for the selected period.</div>
@endif
</div>

{{-- ── Footer ── --}}
<div class="rpt-footer">
    <span>MENRO Waste Management System &middot; Municipality of Madrid, Surigao del Sur</span>
    <span>{{ $title }} &middot; {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</span>
</div>

<script>
    // Auto-open print dialog after a short delay so the page fully renders
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
</script>
</body>
</html>
