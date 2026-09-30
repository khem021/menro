<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title }} — MENRO</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:9pt; color:#1a1a2e; background:#fff; }

    /* ── Header ── */
    .rpt-header { background:#071020; color:#FDB813; padding:14px 18px 10px; }
    .rpt-org { font-size:7pt; color:#7b8fad; text-transform:uppercase; letter-spacing:1px; margin-bottom:2px; }
    .rpt-title { font-size:16pt; font-weight:700; letter-spacing:0.5px; }
    .rpt-meta { margin-top:8px; font-size:8pt; color:#7b8fad; border-top:1px solid #1c2d4a; padding-top:8px; }
    .rpt-meta strong { color:#e8edf5; }

    /* ── Table ── */
    .rpt-table-wrap { padding:14px 18px; }
    table { width:100%; border-collapse:collapse; font-size:8.5pt; }
    thead th {
        background:#0f1d35; color:#e8edf5; font-weight:700; font-size:7.5pt;
        text-transform:uppercase; letter-spacing:0.5px;
        padding:6px 8px; text-align:left;
        border-bottom:2px solid #FDB813;
    }
    tbody tr.row-even { background:#f8fafc; }
    tbody tr.row-odd  { background:#ffffff; }
    tbody td { padding:5px 8px; border-bottom:1px solid #e2e8f0; }

    /* ── Status/category cells ── */
    .cell-green  { background:#dcfce7; color:#14532d; font-weight:600; padding:2px 6px; }
    .cell-red    { background:#fee2e2; color:#7f1d1d; font-weight:600; padding:2px 6px; }
    .cell-amber  { background:#fef3c7; color:#78350f; font-weight:600; padding:2px 6px; }
    .cell-blue   { background:#dbeafe; color:#1e3a5f; font-weight:600; padding:2px 6px; }
    .cell-purple { background:#ede9fe; color:#3b0764; font-weight:600; padding:2px 6px; }
    .cell-gray   { background:#f1f5f9; color:#475569; font-weight:600; padding:2px 6px; }

    /* ── Footer ── */
    .rpt-footer {
        margin-top:16px; padding:8px 18px;
        border-top:1px solid #e2e8f0; font-size:7.5pt; color:#94a3b8;
    }

    /* ── Empty state ── */
    .rpt-empty { padding:24px; text-align:center; color:#94a3b8; font-size:9pt; }
</style>
</head>
<body>

{{-- ── Report header ── --}}
<div class="rpt-header">
    <div class="rpt-org">Republic of the Philippines &middot; MENRO &middot; Municipality of Madrid, Surigao del Sur</div>
    <div class="rpt-title">{{ strtoupper($title) }}</div>
    <div class="rpt-meta">
        Period: <strong>{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Generated: <strong>{{ now()->format('M d, Y h:i A') }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Records: <strong>{{ count($rows) }}</strong>
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
        @foreach($rows as $i => $row)
        <tr class="{{ $i % 2 === 0 ? 'row-odd' : 'row-even' }}">
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
    MENRO Waste Management System &middot; Municipality of Madrid, Surigao del Sur &middot;
    {{ $title }} &middot; {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
</div>

</body>
</html>
