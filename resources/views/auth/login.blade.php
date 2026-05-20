<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — MENRO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="relative overflow-hidden" style="background:#0a1628;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;">

    {{-- Decorative orbs --}}
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full" style="background:rgba(253,184,19,0.04);filter:blur(80px);"></div>
    <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full" style="background:rgba(15,29,53,0.6);filter:blur(80px);"></div>

    <div class="w-full max-w-md relative">

        {{-- Logo --}}
        <div class="flex flex-col items-center gap-3 mb-6">
            <div class="flex items-center justify-center gap-6">
                <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="Bagong Pilipinas" class="w-16 h-16 object-contain drop-shadow-lg">
                <img src="{{ asset('images/menro-logo.png') }}" alt="MENRO Logo" class="w-16 h-16 object-contain drop-shadow-lg">
                <img src="{{ asset('images/madrid-seal.png') }}" alt="Madrid Seal" class="w-16 h-16 object-contain drop-shadow-lg">
            </div>
            <div class="text-center">
                <div class="text-xs font-semibold tracking-widest uppercase" style="color:#7b8fad;">Republic of the Philippines</div>
                <div class="text-xs tracking-wide mt-0.5" style="color:#4a5d7a;">Municipality of Madrid, Surigao del Sur</div>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl p-8 shadow-2xl" style="border:1px solid #1c2d4a;background:#0f1d35;box-shadow:0 25px 60px rgba(0,0,0,0.4);">

            <div class="text-center mb-6">
                <div class="text-2xl font-bold tracking-widest" style="color:#FDB813;">MENRO</div>
                <div class="text-xs mt-0.5" style="color:#7b8fad;">Municipal Environment &amp; Natural Resources Office</div>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 rounded-xl p-4" style="background:rgba(206,17,38,0.1);border:1px solid rgba(206,17,38,0.3);">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" style="color:#CE1126;">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm" style="color:#CE1126;">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:#7b8fad;">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           class="block w-full rounded-xl px-4 py-3 text-sm transition focus:outline-none"
                           style="border:1px solid #1c2d4a;background:#0a1628;color:#e8edf5;"
                           placeholder="Enter your username" autofocus autocomplete="username" required />
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:#7b8fad;">Password</label>
                    <input type="password" id="password" name="password"
                           class="block w-full rounded-xl px-4 py-3 text-sm transition focus:outline-none"
                           style="border:1px solid #1c2d4a;background:#0a1628;color:#e8edf5;"
                           placeholder="••••••••" autocomplete="current-password" required />
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl transition-all duration-150 mt-2"
                        style="background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;box-shadow:0 4px 20px rgba(253,184,19,0.25);letter-spacing:0.05em;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs" style="color:#4a5d7a;position:fixed;bottom:1.25rem;left:0;right:0;">&copy; {{ date('Y') }} MENRO — Municipal Environment &amp; Natural Resources Office</p>
    </div>

</body>
</html>
