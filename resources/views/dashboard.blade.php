<x-app-layout>
    <style>
        /* Shimmer (Glans over het glas) */
        @keyframes shimmer-fast {
            0% { transform: translateX(-150%) skewX(-20deg); }
            100% { transform: translateX(200%) skewX(-20deg); }
        }
        
        /* Aurora (Bewegende achtergrond kleuren) */
        @keyframes aurora {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Float (Zwevend effect voor tekst/iconen) */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        /* Pulse Glow (Kloppende gloed) */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 15px rgba(var(--shadow-color), 0.2); }
            50% { box-shadow: 0 0 25px rgba(var(--shadow-color), 0.5); }
        }

        .animate-shimmer-fast { animation: shimmer-fast 3s infinite ease-in-out; }
        .animate-aurora { background-size: 200% 200%; animation: aurora 10s ease infinite; }
        .animate-float { animation: float 4s ease-in-out infinite; }
        
        /* Speciale classes voor Profit/Loss */
        .theme-profit { --shadow-color: 16, 185, 129; } /* Emerald */
        .theme-loss { --shadow-color: 239, 68, 68; }   /* Red */
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ dashboardView: 'financial', graphView: 'month' }">
        
        <div class="glass-card p-6 rounded-3xl shadow-sm relative overflow-hidden mb-8 bg-white border border-slate-200">
            <div class="flex justify-between items-end mb-3 relative z-10">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Break-even Status</h3>
                    <div class="flex items-baseline gap-2">
                        <div class="text-3xl font-heading font-black text-slate-800 tracking-tight">{{ number_format($breakEvenPercent, 0) }}<span class="text-lg text-slate-400 ml-1">%</span></div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Omzet / Investering</div>
                    <div class="font-bold text-slate-600">€ {{ number_format($totalRevenue, 2, ',', '.') }} / € {{ number_format($totalInvested, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden relative z-10 shadow-inner">
                <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-1000 ease-out relative" style="width: {{ $breakEvenPercent }}%">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/20 animate-[pulse_2s_infinite]"></div>
                </div>
            </div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-purple-500/5 rounded-full blur-3xl"></div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8">
            <div class="bg-white/60 p-1.5 rounded-2xl border border-white/50 shadow-sm flex gap-1 backdrop-blur-sm">
                <button @click="dashboardView = 'financial'" 
                        :class="dashboardView === 'financial' ? 'bg-white text-indigo-600 shadow-md shadow-indigo-100' : 'text-slate-500 hover:bg-white/50'"
                        class="px-6 py-2 text-xs font-bold rounded-xl transition-all">
                    Financieel 💶
                </button>
                <button @click="dashboardView = 'operational'" 
                        :class="dashboardView === 'operational' ? 'bg-white text-indigo-600 shadow-md shadow-indigo-100' : 'text-slate-500 hover:bg-white/50'"
                        class="px-6 py-2 text-xs font-bold rounded-xl transition-all">
                    Operationeel 📦
                </button>
            </div>
            <button onclick="window.print()" class="text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-white flex items-center gap-2 bg-white/50 border border-slate-200 px-5 py-2.5 rounded-xl transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Rapport
            </button>
        </div>

        <div x-show="dashboardView === 'financial'" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-white border border-slate-100 shadow-sm transition-transform hover:scale-[1.02]">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Totale Investering</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-slate-800">€ {{ number_format($totalInvested, 2, ',', '.') }}</p>
            </div>
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-white border border-slate-100 shadow-sm transition-transform hover:scale-[1.02]">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Totale Omzet</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-blue-600">€ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
            </div>
            
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 relative overflow-hidden group transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl
                {{ $realizedProfit >= 0 ? 'theme-profit border-emerald-200/50' : 'theme-loss border-red-200/50' }} border">
                
                <div class="absolute inset-0 opacity-20 animate-aurora
                    {{ $realizedProfit >= 0 
                        ? 'bg-gradient-to-br from-emerald-100 via-teal-100 to-cyan-100' 
                        : 'bg-gradient-to-br from-red-100 via-orange-100 to-rose-100' 
                    }}">
                </div>

                @if($realizedProfit >= 0)
                    <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-400/30 rounded-full blur-[60px] mix-blend-multiply animate-pulse"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-teal-300/30 rounded-full blur-[50px] mix-blend-multiply"></div>
                @else
                    <div class="absolute top-0 right-0 w-48 h-48 bg-red-500/20 rounded-full blur-[60px] mix-blend-multiply animate-pulse"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-orange-400/20 rounded-full blur-[50px] mix-blend-multiply"></div>
                @endif

                <div class="absolute inset-0 overflow-hidden pointer-events-none z-20">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/60 to-transparent animate-shimmer-fast opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                </div>

                <div class="relative z-30 flex justify-between items-start">
                    <h3 class="animate-float text-[10px] font-bold uppercase tracking-widest flex items-center gap-2
                        {{ $realizedProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                        Netto Resultaat
                        
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $realizedProfit >= 0 ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $realizedProfit >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        </span>
                    </h3>
                    
                    <div class="p-2 rounded-xl backdrop-blur-md shadow-sm animate-float border border-white/50
                        {{ $realizedProfit >= 0 ? 'bg-gradient-to-br from-white/80 to-emerald-50/50 text-emerald-600' : 'bg-gradient-to-br from-white/80 to-red-50/50 text-red-500' }}" style="animation-delay: 1s;">
                        @if($realizedProfit >= 0)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                        @endif
                    </div>
                </div>

                <div class="relative z-30 mt-auto">
                    <p class="text-4xl font-heading font-black tracking-tighter drop-shadow-sm transition-all duration-300 group-hover:scale-105 origin-left
                        {{ $realizedProfit >= 0 
                            ? 'text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-800' 
                            : 'text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-rose-600 to-red-800' }}">
                        € {{ number_format($realizedProfit, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-indigo-50 border border-indigo-100 shadow-sm transition-transform hover:scale-[1.02]">
                <h3 class="text-indigo-800 text-[10px] font-bold uppercase tracking-widest">Potentieel Totaal</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-indigo-700">€ {{ number_format($potentialProfit, 2, ',', '.') }}</p>
            </div>
        </div>

        <div x-show="dashboardView === 'operational'" x-cloak x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="glass-card p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Verkocht</h3>
                <p class="text-3xl font-heading font-bold text-emerald-600">{{ $itemsSold }} <span class="text-sm font-medium text-slate-400">items</span></p>
            </div>
            <div class="glass-card p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Voorraad</h3>
                <p class="text-3xl font-heading font-bold text-slate-800">{{ $itemsInStock }} <span class="text-sm font-medium text-slate-400">items</span></p>
            </div>
            <div class="glass-card p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Pakketten</h3>
                <p class="text-3xl font-heading font-bold text-blue-600">{{ $totalParcels }}</p>
            </div>
            <div class="glass-card p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Snelheid (Gem)</h3>
                <p class="text-3xl font-heading font-bold text-slate-800">{{ $avgSellDays }} <span class="text-sm font-medium text-slate-400">dagen</span></p>
            </div>
            <div class="glass-card p-5 rounded-3xl border shadow-sm {{ $oldStockCount > 0 ? 'bg-red-50 border-red-100' : 'border-slate-100 bg-white' }}">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Winkeldochters</h3>
                <p class="text-3xl font-heading font-bold {{ $oldStockCount > 0 ? 'text-red-500' : 'text-emerald-500' }}">{{ $oldStockCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="glass-panel p-8 rounded-3xl shadow-sm lg:col-span-2 bg-white border border-slate-100">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="font-heading font-bold text-xl text-slate-800">Winstverloop</h3>
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-white text-slate-900 shadow-sm">Maand</button>
                    </div>
                </div>
                
                <div class="flex items-end justify-between h-64 gap-4 border-b border-slate-100 pb-2 relative">
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-30">
                        <div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div>
                    </div>

                    @foreach($chartData as $data)
                        <div class="flex-1 flex flex-col items-center group relative h-full justify-end z-10">
                            <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-800 text-white text-xs p-2 rounded-lg pointer-events-none whitespace-nowrap z-20 shadow-xl">
                                <div class="font-bold">{{ $data['label'] }}</div>
                                <div>Omzet: €{{ number_format($data['revenue'], 0) }}</div>
                                <div class="text-indigo-300">Winst: €{{ number_format($data['profit'], 0) }}</div>
                            </div>

                            <div class="w-full max-w-[48px] flex flex-col-reverse h-full relative rounded-t-xl overflow-hidden">
                                <div style="height: {{ $maxRevenue > 0 ? ($data['revenue'] / $maxRevenue) * 100 : 0 }}%" class="w-full bg-slate-100 absolute bottom-0 group-hover:bg-slate-200 transition-colors rounded-t-sm"></div>
                                <div style="height: {{ $maxRevenue > 0 ? max(0, ($data['profit'] / $maxRevenue) * 100) : 0 }}%" class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 absolute bottom-0 opacity-90 transition-all duration-700 shadow-lg shadow-indigo-200 rounded-t-sm"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-3 font-bold uppercase truncate w-full text-center">{{ $data['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-panel p-8 rounded-3xl shadow-sm bg-white border border-slate-100 flex flex-col">
                <h3 class="font-heading font-bold text-xl mb-6 text-slate-800">Top Categorieën</h3>
                <div class="space-y-6 flex-grow">
                    @forelse($topCategories as $index => $cat)
                        <div class="group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">{{ $cat['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-wide">{{ $cat['sold'] }} verkopen</div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                                    € {{ number_format($cat['profit'], 0, ',', '.') }}
                                </div>
                            </div>
                            @php 
                                $maxProfit = $topCategories[0]['profit'] > 0 ? $topCategories[0]['profit'] : 1;
                                $percent = max(5, ($cat['profit'] / $maxProfit) * 100);
                            @endphp
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-slate-400 text-sm italic text-center py-12">
                            Nog geen verkoopdata beschikbaar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>