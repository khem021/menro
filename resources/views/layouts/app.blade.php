<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MENRO Waste Management')</title>

    <script>
        (function () {
            try {
                if (localStorage.getItem('menro-theme') === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>

    {{-- Instrument Sans is bundled through Vite (resources/css/app.css), not fetched
         from a CDN, so it still loads behind a captive portal or content filter. --}}

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
            --input-bg:     #0b1425;
            --chip-border:  #253d5e;
            --overlay:      rgba(0,0,0,0.75);
            --scrollbar-thumb: #1c2d4a;
            --scrollbar-thumb-hover: #263f63;
            --topbar-glass: rgba(10,22,40,0.92);
            --mobnav-bg:    rgba(5,12,24,0.97);
            --grid-line:    rgba(28,45,74,0.8);
            --accent-mid:   #b8860b;
            --accent:       #FDB813;
            --accent-text:  #FDB813;
            --highlight:    #FFD54F;
            --accent-glow:  #FDB81330;
            --text:         #e8edf5;
            --text-muted:   #7b8fad;
            --text-dim:     #4a5d7a;
            --danger:       #CE1126;
            --danger-text:  #f87171;
            --warning:      #FDB813;
            --info:         #60a5fa;
            --info-text:    #60a5fa;
            --success-text: #34d399;
            --violet-text:  #a78bfa;
            --orange-text:  #fb923c;
            --shadow-soft:   rgba(0,0,0,0.35);
            --shadow-strong: rgba(0,0,0,0.55);
        }

        :root[data-theme="light"] {
            --bg:           #eef1f7;
            --sidebar-bg:   #ffffff;
            --card-bg:      #ffffff;
            --card-border:  #dde3ee;
            --input-bg:     #ffffff;
            --chip-border:  #cdd7e6;
            --overlay:      rgba(15,23,42,0.45);
            --scrollbar-thumb: #cbd5e6;
            --scrollbar-thumb-hover: #aebbd4;
            --topbar-glass: rgba(255,255,255,0.85);
            --mobnav-bg:    rgba(255,255,255,0.96);
            --grid-line:    rgba(148,163,184,0.35);
            --accent-text:  #8a5c00;
            --text:         #16233b;
            --text-muted:   #55647e;
            --text-dim:     #8b98b0;
            --danger-text:  #b91c1c;
            --info-text:    #1d4ed8;
            --success-text: #047857;
            --violet-text:  #6d28d9;
            --orange-text:  #c2410c;
            --shadow-soft:   rgba(30,41,59,0.10);
            --shadow-strong: rgba(30,41,59,0.16);
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
            height: 100dvh;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            text-decoration: none;
            flex-shrink: 0;
            border-bottom: 1px solid var(--card-border);
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
            color: var(--accent-text);
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
            min-height: 0;
            padding: 0.5rem 0.625rem;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: var(--card-border) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--card-border); border-radius: 999px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }

        /* Short viewports (laptops that aren't full screen): tighten the sidebar so every link fits */
        @media (min-width: 769px) and (max-height: 900px) {
            .sidebar-brand { height: 56px; }
            .sidebar-nav { padding: 0.25rem 0.625rem; }
            .nav-section { padding: 0.3rem 0.625rem 0.125rem; }
            .nav-item { padding: 0.25rem 0.75rem; font-size: 0.78rem; }
            .sidebar-footer { padding: 0.5rem 0.625rem; }
            .sidebar-footer .user-row { padding: 0.25rem 0.75rem; }
        }
        @media (min-width: 769px) and (max-height: 760px) {
            .sidebar-brand { height: 48px; }
            .nav-section { padding-top: 0.2rem; padding-bottom: 0.1rem; }
            .nav-item { padding: 0.1875rem 0.75rem; }
            .nav-item svg { width: 16px; height: 16px; }
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
        .nav-item.active { background: #FDB81318; color: var(--accent-text); }
        .nav-item.active svg { opacity: 1; color: var(--accent-text); }
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
            width: 100%;
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 0.5rem;
            transition: background .15s, color .15s;
        }
        .logout-btn svg { flex-shrink: 0; opacity: 0.7; }
        .logout-btn:hover,
        .logout-btn:active { background: rgba(206,17,38,0.1); color: var(--danger); }
        .logout-btn:hover svg,
        .logout-btn:active svg { opacity: 1; }

        /* ── Main ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            /* Without this a wide table stretches the whole page instead of
               scrolling inside its card (the flex default is min-width:auto). */
            min-width: 0;
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
        .topbar-icon.active-icon { background: rgba(253,184,19,.12); color: var(--accent-text); }

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

        /* ── Dashboard helpers ── */
        .dash-card-title {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }
        .dash-panel { padding: 0; overflow: hidden; display: flex; flex-direction: column; }
        .dash-panel-head {
            padding: 0.5rem 0.875rem;
            border-bottom: 1px solid var(--card-border);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .dash-panel-body { flex: 1; overflow-y: auto; min-height: 0; }
        .dash-panel-link { font-size: 0.6875rem; color: var(--accent-text); text-decoration: none; font-weight: 500; }

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
        .stat-sub span { color: var(--accent-text); font-weight: 600; }
        .stat-sub span.warn { color: var(--accent-text); }
        .stat-sub span.danger { color: var(--danger-text); }

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
            border-bottom: 1px solid var(--card-border);
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
        .badge-green  { background: rgba(52, 211, 153, 0.12);  color: var(--success-text); }
        .badge-yellow { background: rgba(253, 184,  19, 0.12);  color: var(--accent-text); }
        .badge-red    { background: rgba(248, 113, 113, 0.12);  color: var(--danger-text); }
        .badge-blue   { background: rgba( 96, 165, 250, 0.12);  color: var(--info-text); }
        .badge-gray   { background: var(--card-border);  color: var(--text-muted); }
        .badge-violet { background: rgba(167, 139, 250, 0.12);  color: var(--violet-text); }
        .badge-orange { background: rgba(251, 146,  60, 0.12);  color: var(--orange-text); }
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
            color: var(--danger-text);
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
        .btn-icon-edit:hover { color: var(--accent-text); background: rgba(253,184,19,0.1); }
        .btn-icon-del:hover  { color: var(--danger-text); background: #7f1d1d33; }
        .btn-icon-view:hover { color: var(--info-text); background: #1e3a5f33; }

        .btn-sm {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.275rem 0.625rem;
            font-size: 0.7rem; font-weight: 600; font-family: inherit;
            border-radius: 0.375rem; cursor: pointer;
            border: 1px solid transparent; transition: all .15s; text-decoration: none;
        }
        .btn-sm-green  { background: rgba(52,211,153,0.10); color: var(--success-text); border-color: rgba(52,211,153,0.25); }
        .btn-sm-green:hover  { background: rgba(52,211,153,0.18); }
        .btn-sm-blue   { background: #1e3a5f22; color: var(--info-text); border-color: #1e3a5f55; }
        .btn-sm-blue:hover   { background: #1e3a5f44; }
        .btn-sm-red    { background: #7f1d1d22; color: var(--danger-text); border-color: #7f1d1d55; }
        .btn-sm-red:hover    { background: #7f1d1d44; }
        .btn-sm-yellow { background: #78350f22; color: var(--accent-text); border-color: #78350f55; }
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
        .form-label { font-size: 0.8125rem; font-weight: 500; color: var(--text-muted); }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 0.5625rem 0.875rem;
            font-size: 0.875rem; font-family: inherit;
            color: var(--text); background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.5rem; outline: none; transition: border-color .15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--info); box-shadow: 0 0 0 3px rgba(96,165,250,.12); }
        .form-input::placeholder { color: var(--text-dim); }
        .form-select option { background: var(--card-bg); }
        .form-textarea { resize: vertical; min-height: 70px; }
        .form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--card-border); }
        .form-error { font-size: 0.75rem; color: var(--danger-text); margin-top: 0.25rem; }
        .form-section { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem; }
        .form-section-title { font-size: 0.8125rem; font-weight: 700; color: var(--text); margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--card-border); }

        /* ── Flash messages ── */
        .flash { padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.625rem; }
        .flash-success { background: #1e3f7a22; border: 1px solid #FDB81344; color: var(--accent-text); }
        .flash-error   { background: #7f1d1d22; border: 1px solid #dc262644; color: var(--danger-text); }

        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem; }
        .tab-active   { border-color: var(--accent) !important; color: var(--accent-text) !important; }

        /* ── Toast notifications ── */
        .toast-area { position:fixed;bottom:1.5rem;right:1.5rem;z-index:9990;display:flex;flex-direction:column;gap:0.625rem;pointer-events:none;min-width:280px;max-width:420px; }
        .toast { padding:0.75rem 1rem;border-radius:0.5rem;font-size:0.875rem;display:flex;align-items:flex-start;gap:0.625rem;box-shadow:0 8px 30px rgba(0,0,0,0.5);pointer-events:all;word-break:break-word; }
        .toast-success { background:var(--card-bg);border:1px solid rgba(253,184,19,.3);color:var(--accent-text); }
        .toast-error   { background:var(--card-bg);border:1px solid rgba(206,17,38,.35);color:var(--danger-text); }
        .toast-close { margin-left:auto;flex-shrink:0;background:none;border:none;cursor:pointer;color:currentColor;opacity:0.5;padding:0;font-size:1rem;line-height:1; }
        .toast-close:hover { opacity:1; }
        .toast-cta { display:inline-flex;align-items:center;gap:0.25rem;margin-top:0.375rem;font-size:0.8125rem;font-weight:600;color:var(--accent-text);text-decoration:none;opacity:0.9; }
        .toast-cta:hover { opacity:1; }

        /* ── Topbar breadcrumb ── */
        .topbar-breadcrumb { font-size:0.6875rem;color:var(--text-muted);margin-top:2px;display:flex;align-items:center;gap:0.3rem;flex-wrap:wrap; }
        .topbar-breadcrumb a { color:var(--text-muted);text-decoration:none;transition:color .15s; }
        .topbar-breadcrumb a:hover { color:var(--text); }
        .topbar-breadcrumb .sep { opacity:0.4; }

        /* ── Mobile / Responsive ── */
        .hamburger-btn {
            display:none;align-items:center;justify-content:center;
            width:2.25rem;height:2.25rem;background:none;border:none;cursor:pointer;
            color:var(--text-muted);border-radius:0.5rem;flex-shrink:0;
            transition:background .15s,color .15s;
        }
        .hamburger-btn:hover { background:var(--card-border);color:var(--text); }
        .sidebar-backdrop {
            display:none;position:fixed;inset:0;
            background:rgba(0,0,0,0.65);z-index:150;
            backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform:translateX(-100%);
                transition:transform 0.28s cubic-bezier(0.4,0,0.2,1);
                z-index:200;
            }
            .sidebar.sidebar-open { transform:translateX(0);box-shadow:8px 0 40px rgba(0,0,0,0.55); }
            .sidebar-backdrop { display:block; }
            .main { margin-left:0 !important; }
            .hamburger-btn { display:flex; }
            .page-content { padding:1rem; }
            .topbar { padding:0 1rem;gap:0.5rem; }
            .topbar-date { display:none; }

            /* Tables: horizontal scroll inside cards */
            .card { overflow-x:auto; }
            tbody td, thead th { padding:0.5rem 0.625rem; }

            /* Stack all grid helpers */
            .grid-4 { grid-template-columns:repeat(2,1fr) !important; }
            .grid-3,.grid-2 { grid-template-columns:1fr !important; }
            .grid-main { grid-template-columns:1fr !important; }

            /* Forms */
            .form-grid-2 { grid-template-columns:1fr !important; }
            .page-header { flex-direction:column;gap:0.625rem;align-items:flex-start; }
            .filter-bar { gap:0.5rem; }
            .filter-input { min-width:0;flex:1; }
            dialog { max-width:calc(100vw - 1.5rem);padding:1.25rem; }

            /* Dashboard grids */
            .dash-kpi-grid { grid-template-columns:repeat(2,1fr) !important; }
            .dash-main-grid { grid-template-columns:1fr !important; }
            .dash-charts-grid { grid-template-columns:1fr !important; }
            .dash-analytics-grid { grid-template-columns:1fr !important; }
            .dash-activity-grid { grid-template-columns:1fr !important; }
            .dash-activity-grid > .card { height:auto !important; max-height:280px; }
        }

        @media (max-width: 480px) {
            .dash-kpi-grid { grid-template-columns:1fr !important; }
            .topbar-title { font-size:0.875rem; }
        }

        /* ── Shared mobile page helpers ── */
        .mob-page { display:flex;flex-direction:column;gap:0.5rem; }
        .mob-stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem; }
        .mob-tbl-inner { overflow-x:auto;-webkit-overflow-scrolling:touch; }
        .mob-tbl-inner > table { min-width:520px; }
        .mob-fgroup { display:flex;flex-direction:column;gap:0.25rem; }
        .mob-filter-bar { display:flex;align-items:flex-end;gap:0.625rem;flex-wrap:wrap; }
        .mob-pipeline { display:grid;grid-template-columns:1fr auto 1fr auto 1fr auto 1fr;align-items:stretch;gap:0;flex-shrink:0;border-radius:0.75rem;overflow:hidden;border:1px solid var(--card-border); }
        .mob-brgy-layout { display:flex;gap:0.75rem;flex:1;min-height:0;overflow:hidden; }
        .mob-analytics { display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:0.5rem; }
        .mob-report-type-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem; }
        .mob-report-date-row { display:grid;grid-template-columns:1fr 1fr auto auto;gap:0.625rem;align-items:end; }

        @media (max-width: 768px) {
            /* Remove height locks — pages become naturally scrollable */
            .mob-page { height:auto !important;overflow:visible !important; }
            /* Stat grids: 4 → 2 columns */
            .mob-stats-grid { grid-template-columns:repeat(2,1fr) !important; }
            /* Tbl-card: stop height-locking flex children */
            .mob-page .tbl-card { flex:none !important;min-height:0 !important;overflow:visible !important;height:auto !important; }
            .mob-page .tbl-card .mob-tbl-inner { flex:none !important;max-height:none !important;height:auto !important; }
            /* Filter controls: fluid width */
            .mob-fgroup { flex:1;min-width:140px; }
            .mob-fgroup .form-select,
            .mob-fgroup .form-input { width:100% !important;min-width:0 !important; }
            /* Compliance pipeline: 2-col, hide arrows */
            .mob-pipeline { grid-template-columns:1fr 1fr !important; }
            .pipe-arrow { display:none !important; }
            /* Barangay two-panel: stack */
            .mob-brgy-layout { flex-direction:column !important;overflow:visible !important;height:auto !important; }
            .mob-brgy-layout .brgy-list { width:100% !important;height:260px !important;flex-shrink:0 !important; }
            .mob-brgy-layout .brgy-detail { flex:none !important;height:auto !important;overflow:visible !important; }
            /* Analytics: single column */
            .mob-page .mob-analytics { flex:none !important; }
            .mob-analytics { grid-template-columns:1fr !important;grid-template-rows:auto !important;height:auto !important;overflow:visible !important; }
            .mob-analytics > .card { min-height:240px;height:240px; }
            /* Reports */
            .mob-report-type-grid { grid-template-columns:1fr !important; }
            .mob-report-date-row { grid-template-columns:1fr 1fr !important; }
            .mob-report-date-row > a { justify-content:center; }
            /* Barangay stat strip */
            .mob-brgy-stats { flex-wrap:wrap !important; }
            .mob-brgy-stats > div { flex:1;min-width:140px; }
        }
        @media (max-width: 480px) {
            .mob-stats-grid { grid-template-columns:1fr !important; }
            .mob-report-date-row { grid-template-columns:1fr !important; }
            .mob-analytics > .card { min-height:180px; }
        }
    </style>

    <style>
        /* ── UI Enhancements ── */

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--scrollbar-thumb-hover); }

        /* Page content entrance */
        .page-content { animation: pageIn .3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes pageIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Sidebar subtle gradient */
        .sidebar { background: var(--sidebar-bg); }

        /* Smooth nav item transitions */
        .nav-item { transition: background .18s, color .18s; }
        .nav-item.active { box-shadow: inset 0 0 24px rgba(253,184,19,0.04); }

        /* Button lift & glow */
        .btn-primary  { transition: opacity .15s, transform .15s, box-shadow .2s; }
        .btn-primary:hover  { opacity:1; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(253,184,19,0.32); }
        .btn-primary:active { transform: translateY(0); box-shadow: none; }
        .btn-secondary { transition: all .15s, transform .15s; }
        .btn-secondary:hover { transform: translateY(-1px); }
        .btn-secondary:active { transform: translateY(0); }
        .btn-danger { transition: all .15s, transform .15s; }
        .btn-danger:hover { transform: translateY(-1px); }
        .btn-danger:active { transform: translateY(0); }

        /* Stat card hover lift */
        .stat-card { transition: border-color .2s, box-shadow .2s, transform .2s; }
        .stat-card:hover { border-color: rgba(253,184,19,0.18); box-shadow: 0 8px 28px rgba(0,0,0,0.35); transform: translateY(-2px); }

        /* Card border transition */
        .card { transition: border-color .2s; }

        /* Table row smooth highlight */
        tbody tr { transition: background .12s; }

        /* Progress bar shimmer */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .progress-fill {
            background: linear-gradient(90deg, #1a3668, var(--accent), #1a3668);
            background-size: 200% 100%;
            animation: shimmer 2.5s linear infinite;
        }

        /* Flash fade-in */
        .flash-success, .flash-error { animation: pageIn .25s ease both; }

        /* Topbar glass effect */
        .topbar { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: var(--topbar-glass); }

        /* Modal entrance */
        dialog[open] { animation: modalIn .22s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes modalIn {
            from { opacity: 0; transform: translate(-50%,-52%) scale(0.97); }
            to   { opacity: 1; transform: translate(-50%,-50%) scale(1); }
        }

        /* Skip link: invisible until a keyboard user tabs to it */
        .skip-link {
            position: absolute; left: 0.75rem; top: -3rem; z-index: 100000;
            background: var(--accent); color: #071020; font-weight: 700; font-size: 0.8125rem;
            padding: 0.5rem 0.875rem; border-radius: 0.5rem; text-decoration: none;
            transition: top .15s;
        }
        .skip-link:focus { top: 0.75rem; }
        #main-content:focus { outline: none; }

        /* Visible focus for keyboard users on everything interactive */
        a:focus-visible, button:focus-visible, summary:focus-visible, [tabindex]:focus-visible,
        input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid var(--accent); outline-offset: 2px;
        }

        /* Focus ring consistency */
        .btn-primary:focus-visible, .btn-secondary:focus-visible, .btn-danger:focus-visible {
            outline: 2px solid var(--accent); outline-offset: 2px;
        }

        /* Danger badge pulse */
        .nav-badge.danger { animation: badgePulse 2.5s ease-in-out infinite; }
        @keyframes badgePulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(206,17,38,0); }
            50%      { box-shadow: 0 0 0 4px rgba(206,17,38,0.15); }
        }

        /* Toast slide-in */
        .toast { animation: toastIn .3s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(16px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Icon button hover scale */
        .btn-icon { transition: all .15s, transform .12s; }
        .btn-icon:hover { transform: scale(1.1); }

        /* ── Mobile Bottom Navigation ── */
        .mob-bottom-nav { display: none; }

        @media (max-width: 768px) {
            /* Remove browser default tap flash everywhere */
            * { -webkit-tap-highlight-color: transparent; }

            /* Bottom nav bar */
            .mob-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0; left: 0; right: 0;
                height: 58px;
                padding-bottom: env(safe-area-inset-bottom, 0px);
                background: var(--mobnav-bg);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-top: 1px solid var(--card-border);
                z-index: 200;
                align-items: stretch;
                box-shadow: 0 -8px 32px rgba(0,0,0,0.45);
            }

            .mob-nav-item {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 3px;
                text-decoration: none;
                color: var(--text-dim);
                font-size: 0.5625rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                padding: 6px 2px 4px;
                background: none;
                border: none;
                cursor: pointer;
                font-family: inherit;
                transition: color .15s;
                touch-action: manipulation;
                position: relative;
                -webkit-user-select: none;
                user-select: none;
            }

            .mob-nav-item svg { transition: transform .18s cubic-bezier(.34,1.56,.64,1); }
            .mob-nav-item:active { opacity: 0.65; }
            .mob-nav-item:active svg { transform: scale(0.88) !important; }

            .mob-nav-item.mob-nav-active { color: var(--accent-text); }
            .mob-nav-item.mob-nav-active svg {
                transform: scale(1.08);
                filter: drop-shadow(0 0 5px rgba(253,184,19,0.45));
            }
            .mob-nav-item.mob-nav-active::after {
                content: '';
                position: absolute;
                top: 0; left: 50%;
                transform: translateX(-50%);
                width: 22px; height: 2px;
                background: var(--accent);
                border-radius: 0 0 3px 3px;
                box-shadow: 0 0 8px rgba(253,184,19,0.5);
            }

            /* Notification badge on bottom nav */
            .mob-nav-badge {
                position: absolute;
                top: 4px;
                left: calc(50% + 5px);
                min-width: 15px;
                height: 15px;
                padding: 0 3px;
                background: var(--danger);
                color: #fff;
                font-size: 0.5rem;
                font-weight: 700;
                letter-spacing: 0;
                border-radius: 999px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1.5px solid var(--mobnav-bg);
            }

            /* Push main content above bottom nav */
            .main { padding-bottom: calc(58px + env(safe-area-inset-bottom, 0px)) !important; }

            /* Topbar compact on mobile */
            .topbar { height: 56px !important; }

            /* Better active press on all interactive elements */
            .btn-primary:active, .btn-secondary:active,
            .btn-danger:active, .btn-ghost:active { transform: scale(0.96) !important; }
            .btn-icon:active { transform: scale(0.88) !important; }
            .nav-item:active { background: rgba(253,184,19,0.06) !important; }

            /* Table rows — taller tap targets */
            tbody tr td { padding: 0.75rem 0.75rem !important; }
            thead th { padding: 0.5rem 0.75rem 0.625rem !important; }

            /* Smooth native scrolling */
            .mob-tbl-inner,
            .sidebar-nav,
            .page-content { -webkit-overflow-scrolling: touch; }

            /* Safe-area top padding if browser chrome overlaps */
            .topbar { padding-top: env(safe-area-inset-top, 0px); }
        }

        /* Hide bottom nav when keyboard is visible (screen height shrinks) */
        @media (max-width: 768px) and (max-height: 500px) {
            .mob-bottom-nav { display: none !important; }
            .main { padding-bottom: 0 !important; }
        }

        /* Extra-large touch targets on very small phones */
        @media (max-width: 360px) {
            .mob-nav-item svg { width: 18px !important; height: 18px !important; }
            .btn-primary, .btn-secondary, .btn-danger { min-height: 46px !important; }
        }

        /* ── Mobile / Tablet Responsive Fixes ── */

        /* Minimum touch target size for inputs, selects & buttons */
        .form-input, .form-select, .btn-primary, .btn-secondary, .btn-danger, .btn-ghost {
            min-height: 40px;
        }

        /* Compliance pipeline: horizontal scroll on ALL mobile sizes instead of broken 2-col grid */
        @media (max-width: 900px) {
            .mob-pipeline {
                display: flex !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                border-radius: 0.75rem;
                border: 1px solid var(--card-border);
            }
            .mob-pipeline::-webkit-scrollbar { display: none; }
            .mob-pipeline > button {
                flex: 0 0 auto !important;
                min-width: 150px;
                border-radius: 0 !important;
                border: none !important;
                border-right: 1px solid var(--card-border) !important;
            }
            .mob-pipeline > button:last-child { border-right: none !important; }
            .mob-pipeline > .pipe-arrow {
                flex: 0 0 auto !important;
                display: flex !important;
                border: none !important;
            }
        }

        @media (max-width: 768px) {
            /* Filter inputs and selects always fill their group */
            .mob-fgroup .form-input,
            .mob-fgroup .form-select {
                width: 100% !important;
                min-width: 0 !important;
            }

            /* Filter group fills available space */
            .mob-fgroup { flex: 1 !important; min-width: 140px !important; }

            /* "Add ..." button on its own row at the bottom of filter bar */
            .mob-filter-bar > a.btn-primary,
            .mob-filter-bar > button.btn-primary {
                flex: 1 1 100% !important;
                justify-content: center !important;
                margin-left: 0 !important;
                order: 99;
            }

            /* Tables always scroll horizontally */
            .mob-tbl-inner { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }
            .mob-tbl-inner table { min-width: 540px; }

            /* Pagination wraps */
            nav[aria-label="Pagination Navigation"],
            .pagination { flex-wrap: wrap !important; justify-content: center !important; gap: 0.25rem !important; }

            /* Reduce stat card font on small tablets */
            .mob-stats-grid .card { padding: 0.5rem 0.75rem !important; }

            /* Modal full width on mobile */
            dialog { padding: 1.25rem !important; }
        }

        @media (max-width: 480px) {
            /* Two filter groups per row, search always full width */
            .mob-fgroup { flex: 1 1 calc(50% - 0.4rem) !important; min-width: 0 !important; }
            .mob-filter-bar > .mob-fgroup:first-child { flex: 1 1 100% !important; }

            /* Stat values slightly smaller on tiny screens */
            .mob-stats-grid { gap: 0.375rem !important; }
            .mob-stats-grid .card div[style*="1.375rem"] { font-size: 1.125rem !important; }

            /* Topbar: hide date to give title more room */
            .topbar-date { display: none !important; }

            /* Reduce page padding */
            .page-content { padding: 0.75rem !important; }

            /* Card padding tighter */
            .card { padding: 0.875rem !important; }

            /* Form grid always 1 column */
            .form-grid-2 { grid-template-columns: 1fr !important; }

            /* Modal full screen on very small phones */
            dialog {
                max-width: calc(100vw - 1rem) !important;
                padding: 1rem !important;
            }

            /* Page header stack */
            .page-header { flex-direction: column !important; gap: 0.5rem !important; }
            .page-header-right { width: 100% !important; justify-content: flex-end !important; }
        }

        @media (max-width: 360px) {
            /* Very small phones */
            .mob-stats-grid { grid-template-columns: 1fr 1fr !important; }
            .topbar-title { font-size: 0.8125rem !important; }
        }
    </style>

    @stack('styles')
</head>
<body x-data="{ mobileNav: false }">
<a href="#main-content" class="skip-link">Skip to content</a>
{{-- Livewire request progress bar --}}
<div id="lw-bar" style="position:fixed;top:0;left:0;z-index:99999;height:2px;width:0;background:var(--accent);opacity:0;transition:width .35s ease,opacity .2s;pointer-events:none;box-shadow:0 0 12px var(--accent-glow);"></div>

{{-- Mobile sidebar backdrop --}}
<div class="sidebar-backdrop"
     x-show="mobileNav"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileNav = false"
     style="display:none;"></div>

{{-- ============================================================
     SIDEBAR
     ============================================================ --}}
<aside class="sidebar" :class="{ 'sidebar-open': mobileNav }">
    <a href="{{ route('dashboard') }}" class="sidebar-brand" @click="mobileNav = false">
        <div>
            <div class="brand-name">MENRO MADRID</div>
        </div>
    </a>

    <nav class="sidebar-nav" aria-label="Main">
        <div class="nav-section">Workspace</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" @click="mobileNav = false">
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
        <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" @click="mobileNav = false">
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

        <a href="{{ route('barangays.index') }}" class="nav-item {{ request()->routeIs('barangays.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Barangays
        </a>

        @if(canAccess('System Administrator', 'MENRO Officer'))
        <a href="{{ route('clusters.index') }}" class="nav-item {{ request()->routeIs('clusters.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
            </svg>
            Barangay Clusters
        </a>
        @endif

        {{-- ── Operations: follow steps 1 → 4 ────────────────────────────── --}}
        <div class="nav-section" style="margin-top:.375rem;">Operations</div>

        <a href="{{ route('generators.index') }}" class="nav-item {{ request()->routeIs('generators.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Generators
            <span style="flex:1"></span>
            <span class="nav-step">1</span>
        </a>

        <a href="{{ route('entries.index') }}" class="nav-item {{ request()->routeIs('entries.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Waste Entries
            <span style="flex:1"></span>
            <span class="nav-step">2</span>
        </a>

        @if(canAccess('System Administrator', 'MENRO Officer'))
        <a href="{{ route('collections.index') }}" class="nav-item {{ request()->routeIs('collections.*') ? 'active' : '' }}" @click="mobileNav = false">
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
        @endif

        @if(canAccess('System Administrator', 'MENRO Officer', 'Field Inspector'))
        <a href="{{ route('compliance.index') }}" class="nav-item {{ request()->routeIs('compliance.*') || request()->routeIs('inspections.*') || request()->routeIs('violations.*') || request()->routeIs('incidents.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Compliance
            <span style="flex:1"></span>
            @if($openVio > 0)<span class="nav-badge danger" style="margin-right:.375rem">{{ $openVio }}</span>@endif
            <span class="nav-step">4</span>
        </a>
        @endif

        <a href="{{ route('incidents.index') }}" class="nav-item {{ request()->routeIs('incidents.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Incidents
        </a>

        @if(canAccess('System Administrator', 'MENRO Officer', 'Field Inspector'))
        <a href="{{ route('violation-tickets.index') }}" class="nav-item {{ request()->routeIs('violation-tickets.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Violation
        </a>
        @endif

        {{-- ── Insights ────────────────────────────────────────────────────── --}}
        @if(canAccess('System Administrator', 'MENRO Officer'))
        <div class="nav-section" style="margin-top:.375rem;">Insights</div>

        <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Analytics
        </a>

        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Reports
        </a>

        {{-- ── Admin (System Administrator only) ──────────────────────────── --}}
        @endif

        @if(isAdmin())
        <div class="nav-section" style="margin-top:.375rem;">Admin</div>

        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>

        <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Audit Trail
        </a>

        <a href="{{ route('archive.index') }}" class="nav-item {{ request()->routeIs('archive.*') ? 'active' : '' }}" @click="mobileNav = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M21 8v13H3V8"/>
                <path d="M1 3h22v5H1z"/>
                <path d="M10 12h4"/>
            </svg>
            Archive
        </a>

        <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings*') ? 'active' : '' }}" @click="mobileNav = false">
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
                {{-- If the stored file is missing, fall back to the initials rather
                     than leaving a broken image in the sidebar on every page. --}}
                <img src="{{ asset('storage/avatars/' . $u->avatar) }}" alt="{{ $u->full_name }}"
                     onerror="var f=this.nextElementSibling; if(f){f.hidden=false;} this.remove();"
                     style="width:2rem;height:2rem;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--card-border);">
                <div class="avatar" hidden>{{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}</div>
            @else
                <div class="avatar">{{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}</div>
            @endif
            <div class="user-info">
                <div class="user-name">{{ $u->full_name ?? 'User' }}</div>
                <div class="user-role">{{ authRole() }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</aside>

{{-- ============================================================
     MAIN CONTENT
     ============================================================ --}}
<div class="main">
    <header class="topbar">
        {{-- Hamburger (mobile only) --}}
        <button class="hamburger-btn" @click="mobileNav = !mobileNav" aria-label="Toggle menu">
            <svg x-show="!mobileNav" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg x-show="mobileNav" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <div class="topbar-title" role="heading" aria-level="1">
            @yield('page-title', 'Dashboard')
            @hasSection('page-subtitle')
            <div class="topbar-breadcrumb">@yield('page-subtitle')</div>
            @endif
        </div>
        <div class="topbar-right">
            <span class="topbar-date">{{ now()->format('D, M j Y') }}</span>
            <button type="button" class="topbar-icon" id="themeToggleBtn" onclick="menroToggleTheme()" title="Toggle light / dark theme" aria-label="Toggle light / dark theme">
                <svg id="themeIconSun" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none;">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
                <svg id="themeIconMoon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
            <a href="{{ route('notifications.index') }}"
               class="topbar-icon {{ request()->routeIs('notifications.*') ? 'active-icon' : '' }}"
               style="position:relative;"
               aria-label="Notifications{{ $unread > 0 ? ' (' . $unread . ' unread)' : '' }}" title="Notifications">
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

    <main class="page-content" id="main-content" tabindex="-1">
        @yield('content')
    </main>
</div>

{{-- ============================================================
     TOAST NOTIFICATIONS (floating, auto-dismiss)
     ============================================================ --}}
<div class="toast-area" aria-live="polite">
    @if(session('success'))
    <div x-data="{v:true}" x-show="v" x-init="setTimeout(()=>v=false,4500)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-2"
         class="toast toast-success" role="status">
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
        <button type="button" @click="v=false" class="toast-close" aria-label="Dismiss message">×</button>
    </div>
    @endif
    @if(session('error'))
    <div x-data="{v:true}" x-show="v" x-init="setTimeout(()=>v=false,6000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-2"
         class="toast toast-error" role="alert">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        <div style="flex:1;">{{ session('error') }}</div>
        <button type="button" @click="v=false" class="toast-close" aria-label="Dismiss message">×</button>
    </div>
    @endif
</div>

{{-- ============================================================
     MOBILE BOTTOM NAVIGATION (hidden on desktop via CSS)
     ============================================================ --}}
<nav class="mob-bottom-nav" aria-label="Quick links">

    {{-- Home --}}
    <a href="{{ route('dashboard') }}"
       class="mob-nav-item {{ request()->routeIs('dashboard') ? 'mob-nav-active' : '' }}">
        <svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span>Home</span>
    </a>

    {{-- Entries --}}
    <a href="{{ route('entries.index') }}"
       class="mob-nav-item {{ request()->routeIs('entries.*') ? 'mob-nav-active' : '' }}">
        <svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <span>Entries</span>
    </a>

    {{-- Collections --}}
    @if(canAccess('System Administrator', 'MENRO Officer'))
    <a href="{{ route('collections.index') }}"
       class="mob-nav-item {{ request()->routeIs('collections.*') ? 'mob-nav-active' : '' }}">
        <svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="1" y="3" width="15" height="13"/>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
            <circle cx="5.5" cy="18.5" r="2.5"/>
            <circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
        <span>Collect</span>
    </a>
    @endif

    {{-- Compliance (with open violations badge) --}}
    @if(canAccess('System Administrator', 'MENRO Officer', 'Field Inspector'))
    <a href="{{ route('compliance.index') }}"
       class="mob-nav-item {{ request()->routeIs('compliance.*') || request()->routeIs('inspections.*') || request()->routeIs('violations.*') || request()->routeIs('incidents.*') ? 'mob-nav-active' : '' }}">
        @if(isset($openVio) && $openVio > 0)
            <span class="mob-nav-badge">{{ $openVio > 9 ? '9+' : $openVio }}</span>
        @endif
        <svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <span>Comply</span>
    </a>
    @endif

    {{-- Menu (opens full sidebar; shows badge if unread notifications) --}}
    <button class="mob-nav-item {{ !request()->routeIs('dashboard') && !request()->routeIs('entries.*') && !request()->routeIs('collections.*') && !request()->routeIs('compliance.*') && !request()->routeIs('inspections.*') && !request()->routeIs('violations.*') && !request()->routeIs('incidents.*') ? 'mob-nav-active' : '' }}"
            @click="mobileNav = true">
        @if(isset($unread) && $unread > 0)
            <span class="mob-nav-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
        <svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <span>Menu</span>
    </button>

</nav>

@livewireScripts

{{-- Shared confirmation dialog. Any element with data-confirm="..." asks first;
     data-confirm-label sets the confirm button text, data-confirm-tone="neutral"
     uses the primary style instead of the danger style. --}}
<dialog id="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-message" style="max-width:26rem;">
    <h2 id="confirm-title" class="modal-title" style="margin-bottom:0.75rem;">Please confirm</h2>
    <p id="confirm-message" style="color:var(--text-muted);font-size:0.875rem;line-height:1.55;margin-bottom:1.5rem;overflow-wrap:anywhere;"></p>
    <div style="display:flex;justify-content:flex-end;gap:0.5rem;flex-wrap:wrap;">
        <button type="button" id="confirm-cancel" class="btn-secondary" autofocus>Cancel</button>
        <button type="button" id="confirm-ok" class="btn-danger">Delete</button>
    </div>
</dialog>
<script>
(function () {
    var dlg = document.getElementById('confirm-dialog');
    if (!dlg) return;
    var msg = document.getElementById('confirm-message');
    var ok = document.getElementById('confirm-ok');
    var cancel = document.getElementById('confirm-cancel');
    var pending = null;

    function proceed() {
        var el = pending; pending = null;
        if (!el) return;
        el.setAttribute('data-confirmed', '');
        el.click();
        el.removeAttribute('data-confirmed');
    }

    // Capture phase, so this runs before Livewire's own click handler.
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-confirm]') : null;
        if (!el || el.hasAttribute('data-confirmed')) return;
        e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
        pending = el;
        msg.textContent = el.getAttribute('data-confirm');
        ok.textContent = el.getAttribute('data-confirm-label') || 'Delete';
        ok.className = el.getAttribute('data-confirm-tone') === 'neutral' ? 'btn-primary' : 'btn-danger';
        if (typeof dlg.showModal === 'function') { dlg.showModal(); }
        else if (window.confirm(msg.textContent)) { proceed(); }
    }, true);

    ok.addEventListener('click', function () { dlg.close(); proceed(); });
    cancel.addEventListener('click', function () { pending = null; dlg.close(); });
    dlg.addEventListener('cancel', function () { pending = null; });          // Esc
    dlg.addEventListener('click', function (e) { if (e.target === dlg) { pending = null; dlg.close(); } }); // backdrop
})();
</script>
<script>
// Toasts raised by Livewire actions (see AppServiceProvider): "Entry deleted." etc.
window.addEventListener('toast', function (e) {
    var d = e.detail || {};
    var area = document.querySelector('.toast-area');
    if (!area || !d.message) return;
    var isError = d.type === 'error';
    var el = document.createElement('div');
    el.className = 'toast ' + (isError ? 'toast-error' : 'toast-success');
    el.setAttribute('role', isError ? 'alert' : 'status');
    var text = document.createElement('div');
    text.style.flex = '1';
    text.textContent = d.message;
    var close = document.createElement('button');
    close.type = 'button'; close.className = 'toast-close'; close.textContent = '×';
    close.setAttribute('aria-label', 'Dismiss message');
    function remove() { if (el.parentNode) el.parentNode.removeChild(el); }
    close.addEventListener('click', remove);
    el.appendChild(text); el.appendChild(close);
    area.appendChild(el);
    setTimeout(remove, isError ? 6000 : 4500);
});

function menroApplyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    var sun = document.getElementById('themeIconSun');
    var moon = document.getElementById('themeIconMoon');
    if (sun && moon) {
        sun.style.display = theme === 'light' ? 'block' : 'none';
        moon.style.display = theme === 'light' ? 'none' : 'block';
    }
}
function menroToggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    var next = current === 'light' ? 'dark' : 'light';
    try { localStorage.setItem('menro-theme', next); } catch (e) {}
    menroApplyTheme(next);
    document.dispatchEvent(new CustomEvent('menro:theme-changed', { detail: { theme: next } }));
}
menroApplyTheme(document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark');

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
