<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('heading') · MENRO</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('menro-theme') === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #07111f; --card: #0f1d35; --border: #1c2d4a; --text: #e8edf5; --muted: #9bb0cf;
            --accent: #FDB813; --accent-text: #FDB813; --on-accent: #071020;
        }
        :root[data-theme="light"] {
            --bg: #eef1f7; --card: #ffffff; --border: #dde3ee; --text: #16233b; --muted: #55647e;
            --accent: #FDB813; --accent-text: #8a5c00; --on-accent: #071020;
        }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;
            background: var(--bg); color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        main {
            width: 100%; max-width: 30rem; text-align: center; padding: 2.5rem 1.75rem;
            background: var(--card); border: 1px solid var(--border); border-radius: 1rem;
            box-shadow: 0 20px 40px -20px rgba(0, 0, 0, .45);
        }
        .brand { font-size: .75rem; font-weight: 700; letter-spacing: .12em; color: var(--accent-text); text-transform: uppercase; }
        .code { font-size: 4rem; font-weight: 800; line-height: 1; margin: 1rem 0 .5rem; color: var(--accent-text); letter-spacing: .02em; }
        h1 { font-size: 1.25rem; font-weight: 700; margin-bottom: .625rem; }
        p { color: var(--muted); font-size: .9375rem; line-height: 1.6; overflow-wrap: anywhere; }
        .actions { display: flex; gap: .625rem; justify-content: center; flex-wrap: wrap; margin-top: 1.75rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; min-height: 2.5rem; padding: .5rem 1.125rem;
            border-radius: .5rem; font-size: .875rem; font-weight: 600; text-decoration: none; cursor: pointer;
            border: 1px solid var(--border); background: transparent; color: var(--text); font-family: inherit;
        }
        .btn-primary { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }
        .btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
        .btn:hover { filter: brightness(1.08); }
    </style>
</head>
<body>
    @php
        $signedIn = false;
        try { $signedIn = request()->hasSession() && session('auth_user_id'); } catch (\Throwable $e) {}
    @endphp
    <main>
        <div class="brand">MENRO · Madrid</div>
        <div class="code" aria-hidden="true">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            @if ($signedIn)
                <a class="btn btn-primary" href="{{ route('dashboard') }}">Go to dashboard</a>
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">Go to sign in</a>
            @endif
            <button type="button" class="btn" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">Go back</button>
        </div>
    </main>
</body>
</html>
