<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MENRO Waste Management')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:           #0a1628;
            --sidebar-bg:   #071020;
            --sidebar-w:    240px;
            --card-bg:      #0f1d35;
            --card-border:  #1c2d4a;
            --accent-mid:   #b8860b;
            --accent:       #FDB813;
            --highlight:    #FFD54F;
            --accent-glow:  #FDB81330;
            --text:         #e8edf5;
            --text-muted:   #7b8fad;
            --text-dim:     #4a5d7a;
            --danger:       #CE1126;
            --warning:      #FDB813;
            --info:         #60a5fa;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            overflow: hidden;
        }

        .sidebar-brand {
            height: 72px;
            box-sizing: border-box;
            padding: 0 1.25rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            gap: 0.625rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        .brand-icon {
            width: 2rem; height: 2rem;
            background: linear-gradient(135deg, #1e3f7a, #2952a3);
            border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 12px rgba(253, 184, 19, 0.12);
            flex-shrink: 0;
        }
        .brand-icon svg { color: #fff; }
        .brand-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: 0.08em;
        }
        .brand-tag {
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.5rem 0.625rem;
            overflow: hidden;
        }

        .nav-section {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-dim);
            padding: 0.5rem 0.625rem 0.25rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.8125rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            margin-bottom: 1px;
            position: relative;
        }
        .nav-item svg { flex-shrink: 0; opacity: 0.7; transition: opacity .15s; }
        .nav-item:hover { background: #1e3f7a20; color: var(--text); }
        .nav-item:hover svg { opacity: 1; }
        .nav-item.active { background: #FDB81318; color: var(--accent); }
        .nav-item.active svg { opacity: 1; color: var(--accent); }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 2px 2px 0;
        }

        .nav-badge {
            font-size: 0.6875rem;
            font-weight: 600;
            background: #1e3f7a;
            color: var(--accent);
            padding: 0.1rem 0.4rem;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .nav-badge.danger { background: #7f1d1d; color: var(--danger); }
        .nav-step {
            font-size: 0.6rem;
            font-weight: 700;
            color: var(--text-dim);
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 999px;
            padding: 0.1rem 0.35rem;
            flex-shrink: 0;
            letter-spacing: 0.03em;
        }
        .nav-item.active .nav-step {
            color: rgba(253,184,19,0.65);
            border-color: rgba(253,184,19,0.2);
            background: rgba(253,184,19,0.07);
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 0.875rem 0.625rem;
            border-top: 1px solid var(--card-border);
        }
        .user-row {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }
        .avatar {
            width: 2rem; height: 2rem;
            background: linear-gradient(135deg, #1e3f7a, #2952a3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .user-info { flex: 1; min-width: 0; }
        .user-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role {
            font-size: 0.6875rem;
            color: var(--text-muted);
        }
        .logout-btn {
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
            display: flex;
            transition: color .15s;
        }
        .logout-btn:hover { color: var(--danger); }

        /* ── Main ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: 72px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            gap: 1rem;
            position: sticky;
            top: 0;
            background: var(--bg);
            z-index: 50;
        }
        .topbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .topbar-date {
            font-size: 0.8125rem;
            color: var(--text-muted);
        }
        .topbar-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem; height: 2rem;
            border-radius: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: background .15s, color .15s;
            position: relative;
        }
        .topbar-icon:hover { background: var(--card-border); color: var(--text); }
        .topbar-icon.active-icon { background: rgba(253,184,19,.12); color: var(--accent); }

        .status-dot {
            width: 8px; height: 8px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 6px #22c55e;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .page-content { padding: 1.75rem; flex: 1; }

        /* ── Cards ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.75rem;
            padding: 1.25rem;
        }
        .card-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-title svg { opacity: 0.7; }

        /* ── Stat card ── */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.75rem;
            padding: 1.125rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .stat-header { display: flex; align-items: center; justify-content: space-between; }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
        }
        .stat-icon {
            width: 2rem; height: 2rem;
            border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
            line-height: 1;
        }
        .stat-sub { font-size: 0.75rem; color: var(--text-muted); }
        .stat-sub span { color: var(--accent); font-weight: 600; }
        .stat-sub span.warn { color: var(--warning); }
        .stat-sub span.danger { color: var(--danger); }

        /* ── Grid helpers ── */
        .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
        .grid-2 { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; }
        .grid-main { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; }
        .gap-top { margin-top: 1rem; }
        .gap-top-lg { margin-top: 1.5rem; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-dim);
            text-align: left;
            padding: 0 0.75rem 0.625rem;
            border-bottom: 1px solid var(--card-border);
        }
        tbody td {
            font-size: 0.8125rem;
            color: var(--text-muted);
            padding: 0.625rem 0.75rem;
            border-bottom: 1px solid #152540;
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #1e3f7a10; }
        td.text-main { color: var(--text); font-weight: 500; }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
        }
        .badge-green  { background: rgba(52, 211, 153, 0.12);  color: #34d399; }
        .badge-yellow { background: rgba(253, 184,  19, 0.12);  color: #FDB813; }
        .badge-red    { background: rgba(248, 113, 113, 0.12);  color: #f87171; }
        .badge-blue   { background: rgba( 96, 165, 250, 0.12);  color: #60a5fa; }
        .badge-gray   { background: rgba( 28,  45,  74, 0.90);  color: var(--text-muted); }
        .badge-violet { background: rgba(167, 139, 250, 0.12);  color: #a78bfa; }
        .badge-orange { background: rgba(251, 146,  60, 0.12);  color: #fb923c; }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

        /* ── Progress bar ── */
        .progress-row { display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 0.75rem; }
        .progress-label { display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); }
        .progress-label span:last-child { color: var(--text); font-weight: 600; }
        .progress-track { height: 5px; background: var(--card-border); border-radius: 999px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #1e3f7a, var(--accent)); }

        /* ── Page header ── */
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
        .page-header-left h2 { font-size: 1.125rem; font-weight: 700; color: var(--text); }
        .page-header-left p { font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.25rem; }
        .page-header-right { display: flex; gap: 0.625rem; align-items: center; }

        /* ── Filter bar ── */
        .filter-bar { display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; }
        .filter-input, .filter-select {
            padding: 0.4375rem 0.75rem;
            font-size: 0.8125rem;
            font-family: inherit;
            color: var(--text);
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.5rem;
            outline: none;
            transition: border-color .15s;
        }
        .filter-input { min-width: 200px; }
        .filter-input::placeholder { color: var(--text-dim); }
        .filter-input:focus, .filter-select:focus { border-color: var(--info); }
        .filter-select option { background: var(--card-bg); }

        /* ── Buttons ── */
        .btn-primary {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            color: #071020;
            background: linear-gradient(135deg, #b8860b, #FDB813);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: opacity .15s;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            text-decoration: none;
        }
        .btn-primary:hover { opacity: 0.85; }

        .btn-secondary {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            color: var(--text-muted);
            background: transparent;
            border: 1px solid var(--card-border);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            text-decoration: none;
        }
        .btn-secondary:hover { border-color: var(--text-muted); color: var(--text); }

        .btn-danger {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            color: var(--danger);
            background: #7f1d1d33;
            border: 1px solid #7f1d1d55;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            text-decoration: none;
        }
        .btn-danger:hover { background: #7f1d1d55; }

        .btn-ghost {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            color: var(--text-muted);
            background: transparent;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            text-decoration: none;
        }
        .btn-ghost:hover { color: var(--text); background: var(--card-border); }

        .btn-icon {
            width: 2rem; height: 2rem;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: all .15s;
            color: var(--text-muted);
            text-decoration: none;
        }
        .btn-icon-edit:hover { color: var(--accent); background: rgba(253,184,19,0.1); }
        .btn-icon-del:hover  { color: var(--danger); background: #7f1d1d33; }
        .btn-icon-view:hover { color: var(--info); background: #1e3a5f33; }

        .btn-sm {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.275rem 0.625rem;
            font-size: 0.7rem; font-weight: 600; font-family: inherit;
            border-radius: 0.375rem; cursor: pointer;
            border: 1px solid transparent; transition: all .15s; text-decoration: none;
        }
        .btn-sm-green  { background: rgba(52,211,153,0.10); color: #34d399; border-color: rgba(52,211,153,0.25); }
        .btn-sm-green:hover  { background: rgba(52,211,153,0.18); }
        .btn-sm-blue   { background: #1e3a5f22; color: var(--info); border-color: #1e3a5f55; }
        .btn-sm-blue:hover   { background: #1e3a5f44; }
        .btn-sm-red    { background: #7f1d1d22; color: var(--danger); border-color: #7f1d1d55; }
        .btn-sm-red:hover    { background: #7f1d1d44; }
        .btn-sm-yellow { background: #78350f22; color: var(--warning); border-color: #78350f55; }
        .btn-sm-yellow:hover { background: #78350f44; }

        /* ── Forms & Modals ── */
        dialog {
            background: var(--card-bg); border: 1px solid var(--card-border);
            border-radius: 0.875rem; padding: 1.75rem; color: var(--text);
            max-width: 520px; width: 100%;
            box-shadow: 0 25px 50px -12px #00000080;
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%,-50%); margin: 0;
        }
        dialog::backdrop { background: rgba(0,0,0,.75); backdrop-filter: blur(4px); }
        .modal-title { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--text); display: flex; align-items: center; justify-content: space-between; }
        .modal-close { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem; border-radius: 0.375rem; font-size: 1.25rem; line-height: 1; }
        .modal-close:hover { color: var(--text); }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.375rem; margin-bottom: 0.875rem; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label { font-size: 0.8125rem; font-weight: 500; color: #93afd4; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 0.5625rem 0.875rem;
            font-size: 0.875rem; font-family: inherit;
            color: #e8edf5; background: #0b1425;
            border: 1px solid var(--card-border);
            border-radius: 0.5rem; outline: none; transition: border-color .15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--info); box-shadow: 0 0 0 3px rgba(96,165,250,.12); }
        .form-input::placeholder { color: var(--text-dim); }
        .form-select option { background: var(--card-bg); }
        .form-textarea { resize: vertical; min-height: 70px; }
        .form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--card-border); }
        .form-error { font-size: 0.75rem; color: var(--danger); margin-top: 0.25rem; }
        .form-section { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem; }
        .form-section-title { font-size: 0.8125rem; font-weight: 700; color: var(--text); margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--card-border); }

        /* ── Flash messages ── */
        .flash { padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.625rem; }
        .flash-success { background: #1e3f7a22; border: 1px solid #FDB81344; color: var(--accent); }
        .flash-error   { background: #7f1d1d22; border: 1px solid #dc262644; color: var(--danger); }

        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem; }
        .tab-active   { border-color: var(--accent) !important; color: var(--accent) !important; }

        /* ── Toast notifications ── */
        .toast-area { position:fixed;bottom:1.5rem;right:1.5rem;z-index:9990;display:flex;flex-direction:column;gap:0.625rem;pointer-events:none;min-width:280px;max-width:420px; }
        .toast { padding:0.75rem 1rem;border-radius:0.5rem;font-size:0.875rem;display:flex;align-items:flex-start;gap:0.625rem;box-shadow:0 8px 30px rgba(0,0,0,0.5);pointer-events:all;word-break:break-word; }
        .toast-success { background:var(--card-bg);border:1px solid rgba(253,184,19,.3);color:var(--accent); }
        .toast-error   { background:var(--card-bg);border:1px solid rgba(206,17,38,.35);color:#f87171; }
        .toast-close { margin-left:auto;flex-shrink:0;background:none;border:none;cursor:pointer;color:currentColor;opacity:0.5;padding:0;font-size:1rem;line-height:1; }
        .toast-close:hover { opacity:1; }
        .toast-cta { display:inline-flex;align-items:center;gap:0.25rem;margin-top:0.375rem;font-size:0.8125rem;font-weight:600;color:var(--accent);text-decoration:none;opacity:0.9; }
        .toast-cta:hover { opacity:1; }

        /* ── Topbar breadcrumb ── */
        .topbar-breadcrumb { font-size:0.6875rem;color:var(--text-muted);margin-top:2px;display:flex;align-items:center;gap:0.3rem;flex-wrap:wrap; }
        .topbar-breadcrumb a { color:var(--text-muted);text-decoration:none;transition:color .15s; }
        .topbar-breadcrumb a:hover { color:var(--text); }
        .topbar-breadcrumb .sep { opacity:0.4; }

        /* ── Table loading overlay ── */
        .tbl-card { position:relative; isolation:isolate; }
        .tbl-overlay { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:20;pointer-events:none;border-radius:0.75rem; }
        .tbl-dimmed { opacity:0.2;pointer-events:none; }
        .lw-spinner { width:2.25rem;height:2.25rem;border:2.5px solid rgba(253,184,19,0.18);border-top-color:var(--accent);border-radius:50%;animation:lw-spin .7s linear infinite;filter:drop-shadow(0 0 10px rgba(253,184,19,.5)); }
        @keyframes lw-spin { to { transform:rotate(360deg); } }
    </style>
    @stack('styles')
</head>
<body>
{{-- Livewire request progress bar --}}
<div id="lw-bar" style="position:fixed;top:0;left:0;z-index:99999;height:2px;width:0;background:var(--accent);opacity:0;transition:width .35s ease,opacity .2s;pointer-events:none;box-shadow:0 0 12px var(--accent-glow);"></div>

{{-- ============================================================
     SIDEBAR
     ============================================================ --}}
<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('images/menro-logo.png') }}" alt="MENRO Logo"
             style="width:3.25rem;height:3.25rem;object-fit:contain;flex-shrink:0;">
        <div>
            <div class="brand-name">MENRO</div>
            <div class="brand-tag">Republic of the Philippines</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section">Workspace</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>

        @php
            $unread  = \Illuminate\Support\Facades\Cache::remember('nav:unread:' . session('auth_user_id'), 30, fn() =>
                \App\Models\Notification::where('user_id', session('auth_user_id'))->where('is_read', false)->count()
            );
            $openVio = \Illuminate\Support\Facades\Cache::remember('nav:open_violations', 60, fn() =>
                \App\Models\Violation::where('resolution_status', 'open')->count()
            );
        @endphp
        <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Notifications
            @if($unread > 0)
                <span style="flex:1"></span>
                <span class="nav-badge danger">{{ $unread }}</span>
            @endif
        </a>

        {{-- ── Setup: prerequisite reference data ─────────────────────────── --}}
        <div class="nav-section" style="margin-top:.375rem;">Setup</div>

        <a href="{{ route('barangays.index') }}" class="nav-item {{ request()->routeIs('barangays.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Barangays
        </a>

        {{-- ── Operations: follow steps 1 → 4 ────────────────────────────── --}}
        <div class="nav-section" style="margin-top:.375rem;">Operations</div>

        <a href="{{ route('generators.index') }}" class="nav-item {{ request()->routeIs('generators.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Generators
            <span style="flex:1"></span>
            <span class="nav-step">1</span>
        </a>

        <a href="{{ route('entries.index') }}" class="nav-item {{ request()->routeIs('entries.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Waste Entries
            <span style="flex:1"></span>
            <span class="nav-step">2</span>
        </a>

        <a href="{{ route('collections.index') }}" class="nav-item {{ request()->routeIs('collections.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="1" y="3" width="15" height="13"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            Collections
            <span style="flex:1"></span>
            <span class="nav-step">3</span>
        </a>

        <a href="{{ route('compliance.index') }}" class="nav-item {{ request()->routeIs('compliance.*') || request()->routeIs('inspections.*') || request()->routeIs('violations.*') || request()->routeIs('incidents.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Compliance
            <span style="flex:1"></span>
            @if($openVio > 0)<span class="nav-badge danger" style="margin-right:.375rem">{{ $openVio }}</span>@endif
            <span class="nav-step">4</span>
        </a>

        {{-- ── Insights ────────────────────────────────────────────────────── --}}
        <div class="nav-section" style="margin-top:.375rem;">Insights</div>

        <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Analytics
        </a>

        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Reports
        </a>

        {{-- ── Admin (System Administrator only) ──────────────────────────── --}}
        @if(isAdmin())
        <div class="nav-section" style="margin-top:.375rem;">Admin</div>

        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>

        <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Audit Trail
        </a>

        <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            Settings
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-row">
            @php $u = authUser(); @endphp
            @if($u && $u->avatar)
                <img src="{{ asset('storage/avatars/' . $u->avatar) }}" alt="{{ $u->full_name }}"
                     style="width:2rem;height:2rem;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--card-border);">
            @else
                <div class="avatar">{{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}</div>
            @endif
            <div class="user-info">
                <div class="user-name">{{ $u->full_name ?? 'User' }}</div>
                <div class="user-role">{{ authRole() }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ============================================================
     MAIN CONTENT
     ============================================================ --}}
<div class="main">
    <header class="topbar">
        <div class="topbar-title">
            @yield('page-title', 'Dashboard')
            @hasSection('page-subtitle')
            <div class="topbar-breadcrumb">@yield('page-subtitle')</div>
            @endif
        </div>
        <div class="topbar-right">
            <span class="topbar-date">{{ now()->format('D, M j Y') }}</span>
            <a href="{{ route('notifications.index') }}"
               class="topbar-icon {{ request()->routeIs('notifications.*') ? 'active-icon' : '' }}"
               style="position:relative;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unread > 0)
                    <span style="position:absolute;top:2px;right:2px;width:0.5rem;height:0.5rem;border-radius:50%;background:var(--danger);"></span>
                @endif
            </a>
            <div style="display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--text-muted);">
                <div class="status-dot"></div> Live
            </div>
        </div>
    </header>

    <div class="page-content">
        @yield('content')
    </div>
</div>

{{-- ============================================================
     TOAST NOTIFICATIONS (floating, auto-dismiss)
     ============================================================ --}}
<div class="toast-area">
    @if(session('success'))
    <div x-data="{v:true}" x-show="v" x-init="setTimeout(()=>v=false,4500)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-2"
         class="toast toast-success">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <div style="flex:1;">
            <div>{{ session('success') }}</div>
            @if(session('success_cta'))
            <a href="{{ session('success_cta')['url'] }}" class="toast-cta">
                {{ session('success_cta')['label'] }}
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </div>
        <button @click="v=false" class="toast-close">×</button>
    </div>
    @endif
    @if(session('error'))
    <div x-data="{v:true}" x-show="v" x-init="setTimeout(()=>v=false,6000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-2"
         class="toast toast-error">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        <div style="flex:1;">{{ session('error') }}</div>
        <button @click="v=false" class="toast-close">×</button>
    </div>
    @endif
</div>

@livewireScripts
<script>
document.addEventListener('livewire:load', function () {
    const bar = document.getElementById('lw-bar');
    Livewire.hook('message.sent', () => {
        bar.style.width = '70%';
        bar.style.opacity = '1';
    });
    Livewire.hook('message.processed', () => {
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => { bar.style.width = '0%'; }, 300);
        }, 150);
    });
    Livewire.hook('message.failed', () => {
        bar.style.opacity = '0';
        setTimeout(() => { bar.style.width = '0%'; }, 300);
    });
});
</script>
@stack('scripts')
</body>
</html>
