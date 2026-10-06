<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MENRO</title>

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
        }

        :root[data-theme="light"] {
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

            <div style="text-align:center;margin-bottom:1.75rem;">
                <div style="font-size:1.625rem;font-weight:700;letter-spacing:0.1em;color:var(--title-accent);text-shadow:0 0 48px var(--title-glow);">MENRO</div>
                <div style="font-size:0.75rem;margin-top:0.3rem;color:var(--subtitle-color);letter-spacing:0.01em;">Waste Management Information System</div>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
            <div id="login-alert" role="alert" style="margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:0.75rem;border-radius:0.75rem;padding:0.875rem 1rem;background:var(--error-bg);border:1px solid var(--error-border);">
                <svg style="width:1rem;height:1rem;flex-shrink:0;margin-top:0.125rem;color:var(--error-icon);" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div style="font-size:0.8125rem;color:var(--error-text);">
                    @foreach ($errors->all() as $error)<p class="login-error-line">{{ $error }}</p>@endforeach
                </div>
            </div>
            @endif

            <form id="login-form" action="{{ route('login.post') }}" method="POST" style="display:flex;flex-direction:column;gap:1.125rem;">
                @csrf

                <div>
                    <label for="username" class="field-label">Username</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               class="input-field" placeholder="Enter your username"
                               autofocus autocomplete="username" required />
                    </div>
                </div>

                <div>
                    <label for="password" class="field-label">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password"
                               class="input-field" style="padding-right:2.75rem;"
                               placeholder="••••••••" autocomplete="current-password" required />
                        <button type="button" id="togglePwd" class="pwd-toggle" title="Show password" aria-label="Show password" aria-pressed="false">
                            <svg id="eye-show" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-hide" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    <p id="caps-hint" hidden style="margin-top:0.375rem;font-size:0.75rem;color:var(--title-accent);">Caps Lock is on.</p>
                </div>

                <button type="submit" id="submit-btn" class="btn-submit" style="margin-top:0.25rem;">
                    <svg id="btn-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <svg id="btn-spinner" class="spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="display:none;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:.75"/>
                    </svg>
                    <span id="btn-text">Sign In</span>
                </button>
            </form>
        </div>

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

        // Password visibility toggle
        var pwd = document.getElementById('password');
        var eyeShow = document.getElementById('eye-show');
        var eyeHide = document.getElementById('eye-hide');
        document.getElementById('togglePwd').addEventListener('click', function () {
            var isText = pwd.type === 'text';
            pwd.type = isText ? 'password' : 'text';
            eyeShow.style.display = isText ? 'block' : 'none';
            eyeHide.style.display = isText ? 'none' : 'block';
            this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
            this.setAttribute('title', isText ? 'Show password' : 'Hide password');
            this.setAttribute('aria-pressed', isText ? 'false' : 'true');
        });

        // Coming back with the Back button restores the page from the browser's
        // cache with the button still disabled, so put it back to normal.
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            var btn = document.getElementById('submit-btn');
            btn.disabled = false;
            document.getElementById('btn-icon').style.display = '';
            document.getElementById('btn-spinner').style.display = 'none';
            document.getElementById('btn-text').textContent = 'Sign In';
        });

        // Caps Lock hint
        var capsHint = document.getElementById('caps-hint');
        function checkCaps(e) {
            if (e.getModifierState) { capsHint.hidden = !e.getModifierState('CapsLock'); }
        }
        pwd.addEventListener('keydown', checkCaps);
        pwd.addEventListener('keyup', checkCaps);
        pwd.addEventListener('blur', function () { capsHint.hidden = true; });

        // Lockout countdown: the server says "try again in N seconds"; count it
        // down on the page and keep the button disabled until the lock lifts.
        (function () {
            var alertEl = document.getElementById('login-alert');
            if (!alertEl) return;
            var line = null, seconds = 0, prefix = '';
            alertEl.querySelectorAll('.login-error-line').forEach(function (p) {
                var m = p.textContent.match(/^(.*?)(\d+) seconds?\.?\s*$/);
                if (m) { line = p; prefix = m[1]; seconds = parseInt(m[2], 10); }
            });
            if (!line || !seconds) return;
            var btn = document.getElementById('submit-btn');
            btn.disabled = true;
            document.getElementById('btn-text').textContent = 'Locked';
            var t = setInterval(function () {
                seconds -= 1;
                if (seconds <= 0) {
                    clearInterval(t);
                    line.textContent = 'You can try signing in again now.';
                    btn.disabled = false;
                    document.getElementById('btn-text').textContent = 'Sign In';
                    return;
                }
                line.textContent = prefix + seconds + ' second' + (seconds === 1 ? '' : 's') + '.';
            }, 1000);
        })();

        // Submit loading state
        document.getElementById('login-form').addEventListener('submit', function () {
            var btn = document.getElementById('submit-btn');
            btn.disabled = true;
            document.getElementById('btn-icon').style.display = 'none';
            document.getElementById('btn-spinner').style.display = 'block';
            document.getElementById('btn-text').textContent = 'Signing in…';
        });
    </script>

</body>
</html>
