<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MENRO</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            background: #07111f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Grid background */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(253,184,19,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(253,184,19,0.025) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
        }

        /* Radial vignette over grid */
        .bg-vignette {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 80% at 50% 50%, transparent 40%, #07111f 100%);
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
            background: radial-gradient(circle, rgba(253,184,19,0.06) 0%, transparent 70%);
            animation: floatA 9s ease-in-out infinite;
        }
        .orb-2 {
            width: 380px; height: 380px;
            bottom: -120px; right: -80px;
            background: radial-gradient(circle, rgba(30,63,122,0.25) 0%, transparent 70%);
            animation: floatB 11s ease-in-out infinite;
        }
        .orb-3 {
            width: 260px; height: 260px;
            top: 60%; right: 20%;
            background: radial-gradient(circle, rgba(253,184,19,0.04) 0%, transparent 70%);
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
            background: rgba(13, 24, 46, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(28,45,74,0.9);
            border-radius: 1.25rem;
            padding: 2.25rem;
            box-shadow:
                0 30px 70px rgba(0,0,0,0.55),
                0 0 0 1px rgba(253,184,19,0.05),
                inset 0 1px 0 rgba(255,255,255,0.04);
            position: relative;
            overflow: hidden;
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
            color: #7b8fad;
        }

        /* Input wrapper */
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #3a4f6e;
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
            border: 1px solid #1c2d4a;
            background: rgba(7,17,31,0.7);
            color: #e8edf5;
            transition: border-color .15s, box-shadow .15s, background .15s;
            outline: none;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
            background: rgba(7,17,31,0.9);
        }
        .input-field::placeholder { color: #2e4060; }

        /* Password toggle */
        .pwd-toggle {
            position: absolute;
            right: 0.625rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #3a4f6e;
            padding: 0.3rem;
            border-radius: 0.375rem;
            transition: color .15s, background .15s;
            display: flex;
            align-items: center;
        }
        .pwd-toggle:hover { color: #7b8fad; background: rgba(255,255,255,0.05); }

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
            .logo-row img { width: 2.75rem !important; height: 2.75rem !important; }
            .logo-row .menro-logo { width: 3.25rem !important; height: 3.25rem !important; }
            .logo-row { gap: 1rem !important; }

            /* Input touch targets */
            .input-field { padding-top: 0.875rem; padding-bottom: 0.875rem; min-height: 48px; }
            .btn-submit { padding: 0.9375rem 1rem; font-size: 0.9375rem; }
        }

        @media (max-width: 360px) {
            body { padding: 0.75rem; padding-top: 1.25rem; }
            .logo-row img { width: 2.25rem !important; height: 2.25rem !important; }
            .logo-row .menro-logo { width: 2.75rem !important; height: 2.75rem !important; }
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

    <div class="login-wrap">

        {{-- Logos --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;margin-bottom:1.875rem;">
            <div class="logo-row" style="display:flex;align-items:center;justify-content:center;gap:1.5rem;">
                <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="Bagong Pilipinas"
                     style="width:3.75rem;height:3.75rem;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(0,0,0,0.5));">
                <img class="menro-logo" src="{{ asset('images/menro-logo.png') }}" alt="MENRO Logo"
                     style="width:4.25rem;height:4.25rem;object-fit:contain;filter:drop-shadow(0 4px 20px rgba(253,184,19,0.3));">
                <img src="{{ asset('images/madrid-seal.png') }}" alt="Madrid Seal"
                     style="width:3.75rem;height:3.75rem;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(0,0,0,0.5));">
            </div>
            <div style="text-align:center;line-height:1.5;">
                <div style="font-size:0.6875rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#7b8fad;">Republic of the Philippines</div>
                <div style="font-size:0.6875rem;letter-spacing:0.02em;margin-top:0.125rem;color:#3a4f6e;">Municipality of Madrid, Surigao del Sur</div>
            </div>
        </div>

        {{-- Card --}}
        <div class="login-card">

            <div style="text-align:center;margin-bottom:1.75rem;">
                <div style="font-size:1.625rem;font-weight:700;letter-spacing:0.1em;color:#FDB813;text-shadow:0 0 48px rgba(253,184,19,0.22);">MENRO</div>
                <div style="font-size:0.75rem;margin-top:0.3rem;color:#5a7299;letter-spacing:0.01em;">Waste Management Information System</div>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
            <div style="margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:0.75rem;border-radius:0.75rem;padding:0.875rem 1rem;background:rgba(206,17,38,0.08);border:1px solid rgba(206,17,38,0.22);">
                <svg style="width:1rem;height:1rem;flex-shrink:0;margin-top:0.125rem;color:#f87171;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div style="font-size:0.8125rem;color:#f87171;">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
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
                        <button type="button" id="togglePwd" class="pwd-toggle" title="Show password">
                            <svg id="eye-show" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-hide" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
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

        <p style="text-align:center;font-size:0.6875rem;color:#1e3050;margin-top:1.5rem;">
            &copy; {{ date('Y') }} MENRO — Municipality of Madrid, Surigao del Sur
        </p>
    </div>

    <script>
        // Password visibility toggle
        var pwd = document.getElementById('password');
        var eyeShow = document.getElementById('eye-show');
        var eyeHide = document.getElementById('eye-hide');
        document.getElementById('togglePwd').addEventListener('click', function () {
            var isText = pwd.type === 'text';
            pwd.type = isText ? 'password' : 'text';
            eyeShow.style.display = isText ? 'block' : 'none';
            eyeHide.style.display = isText ? 'none' : 'block';
        });

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
