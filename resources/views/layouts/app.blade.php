<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reseller Pro v4.2</title>
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
    <div class="min-h-screen">
        <!-- Nav -->
        <nav class="glass-header sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-lg shadow-indigo-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <span class="font-heading font-bold text-xl text-slate-800">Reseller Pro <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded border border-indigo-100">v4.2</span></span>
                        
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            @foreach([
                                'dashboard' => 'Dashboard',
                                'inventory.index' => 'Voorraad',
                                'inventory.archive' => 'Archief',
                                'parcels.index' => 'Pakketten',
                                'presets.index' => 'Presets'
                            ] as $route => $label)
                                <a href="{{ route($route) }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold leading-5 transition duration-150 ease-in-out {{ request()->routeIs($route.'*') ? 'border-indigo-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center">
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
    </div>
</body>
</html>