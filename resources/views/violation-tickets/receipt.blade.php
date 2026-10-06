<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ticket {{ $ticket->ticket_number }} — MENRO</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Arial,sans-serif; font-size:10pt; color:#1a1a2e; background:#f1f5f9; }

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
    .print-bar-spacer { height:2.75rem; }

    /* ── Receipt sheet ── */
    .sheet-wrap { padding:1.25rem; display:flex; justify-content:center; }
    .receipt {
        width:100%; max-width:620px; background:#fff;
        border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;
        box-shadow:0 4px 18px rgba(15,23,42,0.08);
    }

    .rcpt-header { background:#071020; color:#FDB813; padding:1.25rem 1.5rem 1rem; text-align:center; }
    .rcpt-logo-row { display:flex; align-items:center; justify-content:center; gap:0.875rem; margin-bottom:0.625rem; }
    .rcpt-logo-seal { width:42px; height:42px; object-fit:contain; }
    .rcpt-logo-word { height:38px; width:auto; object-fit:contain; }
    .rcpt-org { font-size:7.5pt; color:#7b8fad; text-transform:uppercase; letter-spacing:0.1em; }
    .rcpt-muni { font-size:8.5pt; color:#e8edf5; margin-top:0.15rem; }
    .rcpt-title { font-size:15pt; font-weight:700; letter-spacing:0.06em; margin-top:0.5rem; }
    .rcpt-subtitle { font-size:8pt; color:#7b8fad; margin-top:0.15rem; }

    .rcpt-number-band {
        background:#0f1d35; color:#FDB813; text-align:center;
        padding:0.5rem 1rem; font-size:13pt; font-weight:700; letter-spacing:0.08em;
        border-bottom:2px solid #FDB813;
    }
    .rcpt-number-band span { color:#7b8fad; font-size:7.5pt; font-weight:600; display:block; letter-spacing:0.1em; margin-bottom:0.1rem; }

    .rcpt-body { padding:1.25rem 1.5rem; }

    .rcpt-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.875rem 1.25rem; margin-bottom:1rem; }
    .rcpt-field { border-bottom:1px solid #e2e8f0; padding-bottom:0.4rem; }
    .rcpt-field.full { grid-column:1 / -1; }
    .rcpt-label { font-size:7pt; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:0.2rem; }
    .rcpt-value { font-size:10pt; font-weight:600; color:#1a1a2e; }

    .rcpt-offense-row { display:flex; align-items:center; justify-content:space-between; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:0.75rem 1rem; margin-bottom:1rem; }
    .rcpt-offense-badge { font-size:8pt; font-weight:700; padding:0.2rem 0.6rem; border-radius:999px; }
    .rcpt-offense-badge.o1 { background:#dbeafe; color:#1e3a5f; }
    .rcpt-offense-badge.o2 { background:#fef3c7; color:#78350f; }
    .rcpt-offense-badge.o3 { background:#fee2e2; color:#7f1d1d; }
    .rcpt-penalty { font-size:14pt; font-weight:700; color:#b91c1c; }

    .rcpt-legal { font-size:7.5pt; color:#64748b; line-height:1.5; border-top:1px dashed #cbd5e1; padding-top:0.75rem; margin-top:0.25rem; }

    .rcpt-sign-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-top:1.75rem; }
    .rcpt-sign-line { border-top:1px solid #1a1a2e; padding-top:0.35rem; text-align:center; }
    .rcpt-sign-name { font-size:9pt; font-weight:700; }
    .rcpt-sign-role { font-size:7.5pt; color:#64748b; }

    .rcpt-footer {
        padding:0.625rem 1.5rem; border-top:1px solid #e2e8f0; font-size:7.5pt; color:#94a3b8;
        display:flex; justify-content:space-between; align-items:center; background:#f8fafc;
    }

    @media print {
        .print-bar, .print-bar-spacer { display:none !important; }
        html, body { height:100%; background:#fff; }
        .sheet-wrap { padding:0; display:flex; min-height:100vh; }
        .receipt {
            max-width:100%; width:100%; border:none; box-shadow:none; border-radius:0;
            display:flex; flex-direction:column; flex:1;
        }

        /* Give the printed page more visual weight so it fills the sheet */
        .rcpt-header { padding:2rem 2rem 1.5rem; }
        .rcpt-logo-seal { width:56px; height:56px; }
        .rcpt-logo-word { height:50px; }
        .rcpt-org { font-size:9pt; }
        .rcpt-muni { font-size:10pt; }
        .rcpt-title { font-size:20pt; margin-top:0.75rem; }
        .rcpt-subtitle { font-size:9.5pt; }
        .rcpt-number-band { padding:0.875rem 1rem; font-size:17pt; }
        .rcpt-number-band span { font-size:9pt; }

        /* Body fills the remaining page height and centers its content vertically */
        .rcpt-body { flex:1; display:flex; flex-direction:column; justify-content:center; padding:2.5rem 2.5rem; }
        .rcpt-grid { gap:1.25rem 1.75rem; margin-bottom:1.5rem; }
        .rcpt-label { font-size:8.5pt; }
        .rcpt-value { font-size:13pt; }
        .rcpt-offense-row { padding:1.125rem 1.5rem; margin-bottom:1.5rem; }
        .rcpt-offense-badge { font-size:10pt; padding:0.3rem 0.8rem; }
        .rcpt-penalty { font-size:19pt; }
        .rcpt-legal { font-size:9pt; line-height:1.6; padding-top:1rem; }
        .rcpt-sign-grid { margin-top:2.5rem; gap:2.5rem; }
        .rcpt-sign-name { font-size:11pt; }
        .rcpt-sign-role { font-size:9pt; }

        .rcpt-footer { padding:1rem 2rem; font-size:8.5pt; }

        @page { margin:1.25cm; size:A4 portrait; }
    }
</style>
</head>
<body>

{{-- ── Print toolbar ── --}}
<div class="print-bar">
    <span>🧾 Violation Ticket {{ $ticket->ticket_number }}</span>
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="{{ route('violation-tickets.index') }}">← Back to Violation</a>
        <button onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
</div>
<div class="print-bar-spacer"></div>

<div class="sheet-wrap">
<div class="receipt">

    {{-- ── Header ── --}}
    <div class="rcpt-header">
        <div class="rcpt-logo-row">
            <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="Bagong Pilipinas" class="rcpt-logo-seal">
            <img src="{{ asset('images/madrid-palamboon.png') }}" alt="Madrid Palamboon" class="rcpt-logo-word">
            <img src="{{ asset('images/madrid-seal.png') }}" alt="Municipality of Madrid Seal" class="rcpt-logo-seal">
        </div>
        <div class="rcpt-org">Republic of the Philippines</div>
        <div class="rcpt-muni">Municipal Environment &amp; Natural Resources Office &middot; Madrid, Surigao del Sur</div>
        <div class="rcpt-title">VIOLATION TICKET RECEIPT</div>
        <div class="rcpt-subtitle">Issued under R.A. 9003 — Ecological Solid Waste Management Act of 2000</div>
    </div>

    <div class="rcpt-number-band">
        <span>Ticket Number</span>
        {{ $ticket->ticket_number }}
    </div>

    <div class="rcpt-body">

        <div class="rcpt-grid">
            <div class="rcpt-field">
                <div class="rcpt-label">Name of Violator</div>
                <div class="rcpt-value">{{ $ticket->violator_name }}</div>
            </div>
            <div class="rcpt-field">
                <div class="rcpt-label">Date Issued</div>
                <div class="rcpt-value">{{ \Carbon\Carbon::parse($ticket->issued_date)->format('F d, Y') }}</div>
            </div>
            <div class="rcpt-field full">
                <div class="rcpt-label">Address</div>
                <div class="rcpt-value">{{ $ticket->address }}</div>
            </div>
            <div class="rcpt-field full">
                <div class="rcpt-label">Type of Violation</div>
                <div class="rcpt-value">
                    {{ \App\Models\ViolationTicket::VIOLATION_TYPES[$ticket->violation_type] ?? $ticket->violation_type }}
                    @if($ticket->violation_type === 'other' && $ticket->other_violation_description)
                        — {{ $ticket->other_violation_description }}
                    @endif
                </div>
            </div>
            @if($ticket->remarks)
            <div class="rcpt-field full">
                <div class="rcpt-label">Remarks</div>
                <div class="rcpt-value" style="font-weight:400;">{{ $ticket->remarks }}</div>
            </div>
            @endif
        </div>

        <div class="rcpt-offense-row">
            <div>
                <div class="rcpt-label" style="margin-bottom:0.3rem;">Offense</div>
                @php $oc = match((int) $ticket->offense_number) {1=>'o1',2=>'o2',default=>'o3'}; @endphp
                <span class="rcpt-offense-badge {{ $oc }}">{{ \App\Models\ViolationTicket::offenseLabel($ticket->offense_number) }}</span>
            </div>
            <div style="text-align:right;">
                <div class="rcpt-label" style="margin-bottom:0.3rem;">Penalty Amount</div>
                <div class="rcpt-penalty">₱{{ number_format($ticket->penalty_amount, 2) }}</div>
            </div>
        </div>

        <div class="rcpt-legal">
            This ticket is issued pursuant to Republic Act No. 9003 (Ecological Solid Waste Management Act of 2000)
            and the Municipal Ordinance on Solid Waste Management of Madrid, Surigao del Sur. The penalty indicated
            above must be settled at the Municipal Treasurer's Office within fifteen (15) days from the date of
            issuance. Failure to comply may result in further administrative or legal action.
        </div>

        <div class="rcpt-sign-grid">
            <div class="rcpt-sign-line">
                <div class="rcpt-sign-name">{{ $ticket->issuedBy->full_name ?? '—' }}</div>
                <div class="rcpt-sign-role">Issuing Officer @if($ticket->issuedBy?->role_name) &middot; {{ $ticket->issuedBy->role_name }} @endif</div>
            </div>
            <div class="rcpt-sign-line">
                <div class="rcpt-sign-name">&nbsp;</div>
                <div class="rcpt-sign-role">Violator's Signature</div>
            </div>
        </div>

    </div>

    <div class="rcpt-footer">
        <span>MENRO Waste Management System &middot; Municipality of Madrid, Surigao del Sur</span>
        <span>Printed {{ now()->format('M d, Y h:i A') }}</span>
    </div>

</div>
</div>

</body>
</html>
