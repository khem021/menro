{{-- Shell for the signed-out pages (sign-in, forgot password): background,
     theme toggle, logos and the card frame. Each page fills the card. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Stops phone auto-dark modes repainting the light theme; see layouts/app. --}}
    <meta name="color-scheme" content="dark light">
    <title>@yield('title') — MENRO</title>

    <script>
        (function () {
            try {
                if (localStorage.getItem('menro-theme') === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>

    {{-- Self-hosted Instrument Sans. This page writes its own styles, so it pulls
         in the font-only entry rather than app.css and Tailwind's preflight. --}}
    @vite(['resources/css/fonts.css'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --login-bg:        #07111f;
            --grid-line:       rgba(253,184,19,0.025);
            --orb-1:           rgba(253,184,19,0.06);
            --orb-2:           rgba(30,63,122,0.25);
            --orb-3:           rgba(253,184,19,0.04);
            --card-bg:         rgba(13,24,46,0.88);
            --card-border:     rgba(28,45,74,0.9);
            --card-shadow:     rgba(0,0,0,0.55);
            --label-color:     #7b8fad;
            --icon-color:      #3a4f6e;
            --input-border:    #1c2d4a;
            --input-bg:        rgba(7,17,31,0.7);
            --input-bg-focus:  rgba(7,17,31,0.9);
            --input-text:      #e8edf5;
            --input-placeholder: #2e4060;
            --toggle-color:    #3a4f6e;
            --toggle-hover:    #7b8fad;
            --title-accent:    #FDB813;
            --title-glow:      rgba(253,184,19,0.22);
            --subtitle-color:  #5a7299;
            --copyright-color: #1e3050;
            --logo-shadow:     rgba(0,0,0,0.5);
            --error-bg:        rgba(206,17,38,0.08);
            --error-border:    rgba(206,17,38,0.22);
            --error-icon:      #f87171;
            --error-text:      #f87171;
            --theme-icon-bg-hover: rgba(255,255,255,0.06);
            color-scheme: dark;
        }

        :root[data-theme="light"] {
            color-scheme: only light;
            --login-bg:        #eef1f7;
            --grid-line:       rgba(30,63,122,0.05);
            --orb-1:           rgba(253,184,19,0.12);
            --orb-2:           rgba(30,63,122,0.10);
            --orb-3:           rgba(253,184,19,0.08);
            --card-bg:         rgba(255,255,255,0.9);
            --card-border:     #dde3ee;
            --card-shadow:     rgba(30,41,59,0.14);
            --label-color:     #55647e;
            --icon-color:      #94a3b8;
            --input-border:    #cdd7e6;
            --input-bg:        rgba(255,255,255,0.9);
            --input-bg-focus:  #ffffff;
            --input-text:      #16233b;
            --input-placeholder: #9aa7bd;
            --toggle-color:    #94a3b8;
            --toggle-hover:    #55647e;
            --title-accent:    #8a5c00;
            --title-glow:      rgba(138,92,0,0.12);
            --subtitle-color:  #55647e;
            --copyright-color: #9aa7bd;
            --logo-shadow:     rgba(30,41,59,0.18);
            --error-bg:        rgba(206,17,38,0.06);
            --error-border:    rgba(206,17,38,0.22);
            --error-icon:      #dc2626;
            --error-text:      #b91c1c;
            --theme-icon-bg-hover: rgba(15,23,42,0.06);
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            background: var(--login-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: background .2s;
        }

        /* Grid background */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
        }

        /* Radial vignette over grid */
        .bg-vignette {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 80% at 50% 50%, transparent 40%, var(--login-bg) 100%);
            pointer-events: none;
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
        }
        .orb-1 {
            width: 480px; height: 480px;
            top: -160px; left: -120px;
            background: radial-gradient(circle, var(--orb-1) 0%, transparent 70%);
            animation: floatA 9s ease-in-out infinite;
        }
        .orb-2 {
            width: 380px; height: 380px;
            bottom: -120px; right: -80px;
            background: radial-gradient(circle, var(--orb-2) 0%, transparent 70%);
            animation: floatB 11s ease-in-out infinite;
        }
        .orb-3 {
            width: 260px; height: 260px;
            top: 60%; right: 20%;
            background: radial-gradient(circle, var(--orb-3) 0%, transparent 70%);
            animation: floatC 7s ease-in-out infinite;
        }
        @keyframes floatA {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(20px,-25px); }
        }
        @keyframes floatB {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(-15px,20px); }
        }
        @keyframes floatC {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(10px,-18px); }
        }

        /* Theme toggle */
        .theme-toggle {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            background: none;
            border: 1px solid var(--card-border);
            color: var(--label-color);
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .theme-toggle:hover { background: var(--theme-icon-bg-hover); color: var(--input-text); }

        /* Wrapper entrance */
        .login-wrap {
            width: 100%;
            max-width: 27rem;
            position: relative;
            animation: slideUp .5s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Card */
        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--card-border);
            border-radius: 1.25rem;
            padding: 2.25rem;
            box-shadow:
                0 30px 70px var(--card-shadow),
                0 0 0 1px rgba(253,184,19,0.05),
                inset 0 1px 0 rgba(255,255,255,0.04);
            position: relative;
            overflow: hidden;
            transition: background .2s, border-color .2s, box-shadow .2s;
        }
        /* Shimmer top border */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(253,184,19,0.45) 50%, transparent 100%);
        }

        /* Labels */
        .field-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.5rem;
            color: var(--label-color);
        }

        /* Input wrapper */
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--icon-color);
            pointer-events: none;
            transition: color .15s;
        }
        .input-wrap:focus-within .input-icon { color: #60a5fa; }

        .input-field {
            display: block;
            width: 100%;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem 0.75rem 2.625rem;
            font-size: 0.875rem;
            font-family: inherit;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--input-text);
            transition: border-color .15s, box-shadow .15s, background .15s;
            outline: none;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
            background: var(--input-bg-focus);
        }
        .input-field::placeholder { color: var(--input-placeholder); }

        /* Password toggle */
        .pwd-toggle {
            position: absolute;
            right: 0.625rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--toggle-color);
            padding: 0.3rem;
            border-radius: 0.375rem;
            transition: color .15s, background .15s;
            display: flex;
            align-items: center;
        }
        .pwd-toggle:hover { color: var(--toggle-hover); background: var(--theme-icon-bg-hover); }

        /* Submit */
        .btn-submit {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8125rem 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            font-family: inherit;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #b8860b 0%, #FDB813 50%, #e8a800 100%);
            background-size: 200% 100%;
            background-position: 0 0;
            color: #07111f;
            letter-spacing: 0.04em;
            box-shadow: 0 4px 20px rgba(253,184,19,0.28), 0 1px 3px rgba(0,0,0,0.35);
            transition: transform .15s, box-shadow .2s, background-position .35s, opacity .15s;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 30px rgba(253,184,19,0.42), 0 1px 3px rgba(0,0,0,0.35);
            background-position: 100% 0;
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.75; cursor: not-allowed; transform: none; }

        .spin { animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Forgot-password link and the help page behind it */
        .forgot-link {
            font-size: 0.75rem;
            color: var(--label-color);
            text-decoration: none;
            border-radius: 0.25rem;
            transition: color .15s;
        }
        .forgot-link:hover,
        .forgot-link:focus-visible { color: var(--title-accent); text-decoration: underline; }
        .help-title { font-size: 1.0625rem; font-weight: 700; color: var(--input-text); margin-bottom: 0.625rem; }
        .help-text { font-size: 0.8125rem; line-height: 1.65; color: var(--label-color); }
        .help-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin: 1.125rem 0;
            padding-left: 1rem;
            border-left: 2px solid var(--card-border);
        }
        .help-list strong { color: var(--input-text); font-weight: 600; }

        /* ── Mobile responsiveness ── */
        @media (max-width: 480px) {
            body { padding: 1rem; align-items: flex-start; padding-top: 2rem; }
            .login-wrap { max-width: 100%; }
            .login-card { padding: 1.5rem 1.25rem; }

            /* Shrink logos and stack tighter */
            .logo-row img.logo-seal { width: 2.75rem !important; height: 2.75rem !important; }
            .logo-row img.logo-word { height: 2.25rem !important; width: auto !important; }
            .logo-row { gap: 1rem !important; }

            /* Input touch targets */
            .input-field { padding-top: 0.875rem; padding-bottom: 0.875rem; min-height: 48px; }
            .btn-submit { padding: 0.9375rem 1rem; font-size: 0.9375rem; }
        }

        @media (max-width: 360px) {
            body { padding: 0.75rem; padding-top: 1.25rem; }
            .logo-row img.logo-seal { width: 2.25rem !important; height: 2.25rem !important; }
            .logo-row img.logo-word { height: 1.75rem !important; width: auto !important; }
            .logo-row { gap: 0.75rem !important; }
        }
    </style>
</head>
<body>

    <div class="bg-grid"></div>
    <div class="bg-vignette"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <button type="button" class="theme-toggle" id="themeToggleBtn" onclick="menroToggleTheme()" title="Toggle light / dark theme" aria-label="Toggle light / dark theme">
        <svg id="themeIconSun" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none;">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
        </svg>
        <svg id="themeIconMoon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
    </button>

    <div class="login-wrap">

        {{-- Logos --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;margin-bottom:1.875rem;">
            <div class="logo-row" style="display:flex;align-items:center;justify-content:center;gap:1.5rem;">
                <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="Bagong Pilipinas" class="logo-seal"
                     style="width:3.75rem;height:3.75rem;object-fit:contain;filter:drop-shadow(0 4px 14px var(--logo-shadow));">
                <img src="{{ asset('images/madrid-palamboon.png') }}" alt="Madrid Palamboon" class="logo-word"
                     style="height:3.75rem;width:auto;object-fit:contain;filter:drop-shadow(0 4px 14px var(--logo-shadow));">
                <img src="{{ asset('images/madrid-seal.png') }}" alt="Madrid Seal" class="logo-seal"
                     style="width:3.75rem;height:3.75rem;object-fit:contain;filter:drop-shadow(0 4px 14px var(--logo-shadow));">
            </div>
            <div style="text-align:center;line-height:1.5;">
                <div style="font-size:0.6875rem;font-weight:700;letter-spacing:0.02em;text-transform:uppercase;color:var(--label-color);">Municipality of Madrid, Surigao del Sur</div>
            </div>
        </div>

        {{-- Card --}}
        <div class="login-card">
            @yield('card')
        </div>

        @yield('below-card')

        <p style="text-align:center;font-size:0.6875rem;color:var(--copyright-color);margin-top:1.5rem;">
            &copy; {{ date('Y') }} MENRO — Municipality of Madrid, Surigao del Sur
        </p>
    </div>

    <script>
        // Theme toggle
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
        }
        menroApplyTheme(document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark');
    </script>

    @stack('scripts')

</body>
</html>
