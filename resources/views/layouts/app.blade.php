<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reseller Pro v4.2</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fafc; 
            color: #334155;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,0) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,0) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,0) 0, transparent 50%);
            background-attachment: fixed;
        }
        h1, h2, h3, h4, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(20px); 
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5); 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased">
    @php
        $layoutMode = Auth::check() ? (Auth::user()->layout ?? 'top') : 'top';
    @endphp

    <div class="min-h-screen">
        @if($layoutMode === 'sidebar')
            <div class="flex min-h-screen">
                <!-- Sidebar -->

                <aside class="w-80 bg-white border-r border-slate-200 shadow-lg sticky top-0 h-screen flex flex-col">
                    <div class="p-7 border-b border-slate-100 flex items-center gap-4">
                        <img src="/logo.png" alt="Logo" class="w-20 h-20 object-contain" />
                        <span class="font-heading font-extrabold text-2xl text-slate-800 tracking-tight">Reseller Pro</span>
                    </div>

                    <nav class="flex-1 p-6 space-y-2">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-2 tracking-widest">Navigatie</div>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-bold transition {{ request()->routeIs('dashboard*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-bold transition {{ request()->routeIs('inventory*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                    Voorraad
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('parcels.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-bold transition {{ request()->routeIs('parcels*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                                    Pakketten
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('presets.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-bold transition {{ request()->routeIs('presets*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                    Presets
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <div class="mt-auto p-6 border-t border-slate-100 space-y-3">
                        <form method="POST" action="{{ route('dashboard.layout.toggle') }}">
                            @csrf
                            <button class="w-full text-xs font-bold text-slate-500 hover:text-slate-800 bg-slate-50 px-3 py-2 rounded-xl transition">Menu boven</button>
                        </form>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-xs font-bold text-red-500 hover:text-red-600 bg-red-50 px-3 py-2 rounded-xl transition">Uitloggen</button>
                        </form>
                    </div>
                </aside>

                <!-- Content -->
                <main class="flex-1 py-8">
                    {{ $slot }}
                </main>
            </div>
        @else
            <!-- Top Nav -->
            <nav class="glass-header sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center gap-3">
                            <img src="/logo.png" alt="Logo" class="w-16 h-16 object-contain" />
                            <span class="font-heading font-bold text-xl text-slate-800">Reseller Pro <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded border border-indigo-100">v4.2</span></span>
                            
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                @foreach([
                                    'dashboard' => 'Dashboard',
                                    'inventory.index' => 'Voorraad',
                                    'parcels.index' => 'Pakketten',
                                    'presets.index' => 'Presets'
                                ] as $route => $label)
                                    <a href="{{ route($route) }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold leading-5 transition duration-150 ease-in-out {{ request()->routeIs($route.'*') ? 'border-indigo-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('dashboard.layout.toggle') }}">
                                @csrf
                                <button class="text-xs font-bold text-slate-500 hover:text-slate-800 bg-slate-50 px-3 py-2 rounded-xl transition">Menu links</button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="text-sm text-slate-400 hover:text-red-500 font-bold uppercase transition">Uitloggen</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <main class="py-8">
                {{ $slot }}
            </main>
        @endif
    </div>
</body>
</html>