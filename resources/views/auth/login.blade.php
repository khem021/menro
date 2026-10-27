@extends('layouts.guest')

@section('title', 'Sign In')

@section('card')

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
                    <div style="margin-top:0.5rem;text-align:right;">
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
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
@endsection

@push('scripts')
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
@endpush
