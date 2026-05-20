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
            background: #0a1628;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        input:focus { outline: none; border-color: #60a5fa !important; box-shadow: 0 0 0 3px rgba(96,165,250,0.15); }
        button:hover { opacity: 0.88; }
    </style>
</head>
<body>

    {{-- Decorative orbs --}}
    <div style="position:absolute;top:-10rem;left:-10rem;width:37.5rem;height:37.5rem;border-radius:50%;background:rgba(253,184,19,0.04);filter:blur(80px);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-10rem;right:-10rem;width:31.25rem;height:31.25rem;border-radius:50%;background:rgba(15,29,53,0.6);filter:blur(80px);pointer-events:none;"></div>

    <div style="width:100%;max-width:28rem;position:relative;">

        {{-- Logos --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;">
                <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="Bagong Pilipinas"
                     style="width:4rem;height:4rem;object-fit:contain;">
                <img src="{{ asset('images/menro-logo.png') }}" alt="MENRO Logo"
                     style="width:4rem;height:4rem;object-fit:contain;">
                <img src="{{ asset('images/madrid-seal.png') }}" alt="Madrid Seal"
                     style="width:4rem;height:4rem;object-fit:contain;">
            </div>
            <div style="text-align:center;">
                <div style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:#7b8fad;">Republic of the Philippines</div>
                <div style="font-size:0.7rem;letter-spacing:0.025em;margin-top:0.125rem;color:#4a5d7a;">Municipality of Madrid, Surigao del Sur</div>
            </div>
        </div>

        {{-- Card --}}
        <div style="border-radius:1rem;padding:2rem;border:1px solid #1c2d4a;background:#0f1d35;box-shadow:0 25px 60px rgba(0,0,0,0.4);">

            <div style="text-align:center;margin-bottom:1.5rem;">
                <div style="font-size:1.5rem;font-weight:700;letter-spacing:0.1em;color:#FDB813;">MENRO</div>
                <div style="font-size:0.75rem;margin-top:0.125rem;color:#7b8fad;">Municipal Environment &amp; Natural Resources Office</div>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
            <div style="margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:0.75rem;border-radius:0.75rem;padding:1rem;background:rgba(206,17,38,0.1);border:1px solid rgba(206,17,38,0.3);">
                <svg style="width:1rem;height:1rem;flex-shrink:0;margin-top:0.125rem;color:#CE1126;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div style="font-size:0.875rem;color:#CE1126;">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" style="display:flex;flex-direction:column;gap:1.25rem;">
                @csrf
                <div>
                    <label for="username" style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;color:#7b8fad;">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           style="display:block;width:100%;border-radius:0.75rem;padding:0.75rem 1rem;font-size:0.875rem;font-family:inherit;border:1px solid #1c2d4a;background:#0a1628;color:#e8edf5;transition:border-color .15s;"
                           placeholder="Enter your username" autofocus autocomplete="username" required />
                </div>
                <div>
                    <label for="password" style="display:block;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;color:#7b8fad;">Password</label>
                    <input type="password" id="password" name="password"
                           style="display:block;width:100%;border-radius:0.75rem;padding:0.75rem 1rem;font-size:0.875rem;font-family:inherit;border:1px solid #1c2d4a;background:#0a1628;color:#e8edf5;transition:border-color .15s;"
                           placeholder="••••••••" autocomplete="current-password" required />
                </div>
                <button type="submit"
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:700;font-family:inherit;border-radius:0.75rem;border:none;cursor:pointer;background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;box-shadow:0 4px 20px rgba(253,184,19,0.25);letter-spacing:0.05em;margin-top:0.25rem;transition:opacity .15s;">
                    <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign In
                </button>
            </form>
        </div>

        <p style="text-align:center;font-size:0.7rem;color:#4a5d7a;margin-top:1.5rem;">&copy; {{ date('Y') }} MENRO — Municipal Environment &amp; Natural Resources Office</p>
    </div>

</body>
</html>
