<x-app-layout>
    <!-- Alpine State -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ dashboardView: 'financial', graphView: 'month' }">
        
        <!-- Break Even Progress Bar -->
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
            <!-- Deco blobs -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-purple-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Controls Toolbar -->
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

        <!-- VIEW: FINANCIAL -->
        <div x-show="dashboardView === 'financial'" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card 1 -->
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-white border border-slate-100 shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Totale Investering</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-slate-800">€ {{ number_format($totalInvested, 2, ',', '.') }}</p>
            </div>
            <!-- Card 2 -->
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-white border border-slate-100 shadow-sm">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Totale Omzet</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-blue-600">€ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
            </div>
            <!-- Card 3 -->
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 shadow-sm {{ $realizedProfit >= 0 ? 'bg-emerald-50 border-emerald-100' : 'bg-white border-slate-100' }}">
                <h3 class="{{ $realizedProfit >= 0 ? 'text-emerald-600' : 'text-slate-400' }} text-[10px] font-bold uppercase tracking-widest">Gerealiseerde Winst</h3>
                <p class="text-3xl font-heading font-bold tracking-tight {{ $realizedProfit >= 0 ? 'text-emerald-600' : 'text-slate-800' }}">€ {{ number_format($realizedProfit, 2, ',', '.') }}</p>
            </div>
            <!-- Card 4 -->
            <div class="glass-card p-6 rounded-3xl flex flex-col justify-between h-32 bg-indigo-50 border border-indigo-100 shadow-sm">
                <h3 class="text-indigo-800 text-[10px] font-bold uppercase tracking-widest">Potentieel Totaal</h3>
                <p class="text-3xl font-heading font-bold tracking-tight text-indigo-700">€ {{ number_format($potentialProfit, 2, ',', '.') }}</p>
            </div>
        </div>

        <!-- VIEW: OPERATIONAL -->
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
            
            <!-- Grafiek -->
            <div class="glass-panel p-8 rounded-3xl shadow-sm lg:col-span-2 bg-white border border-slate-100">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="font-heading font-bold text-xl text-slate-800">Winstverloop</h3>
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-white text-slate-900 shadow-sm">Maand</button>
                    </div>
                </div>
                
                <div class="flex items-end justify-between h-64 gap-4 border-b border-slate-100 pb-2 relative">
                    <!-- Achtergrond lijntjes -->
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-30">
                        <div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div><div class="w-full h-px bg-slate-200"></div>
                    </div>

                    @foreach($chartData as $data)
                        <div class="flex-1 flex flex-col items-center group relative h-full justify-end z-10">
                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-800 text-white text-xs p-2 rounded-lg pointer-events-none whitespace-nowrap z-20 shadow-xl">
                                <div class="font-bold">{{ $data['label'] }}</div>
                                <div>Omzet: €{{ number_format($data['revenue'], 0) }}</div>
                                <div class="text-indigo-300">Winst: €{{ number_format($data['profit'], 0) }}</div>
                            </div>

                            <div class="w-full max-w-[48px] flex flex-col-reverse h-full relative rounded-t-xl overflow-hidden">
                                <!-- Omzet balk (achtergrond) -->
                                <div style="height: {{ $maxRevenue > 0 ? ($data['revenue'] / $maxRevenue) * 100 : 0 }}%" class="w-full bg-slate-100 absolute bottom-0 group-hover:bg-slate-200 transition-colors rounded-t-sm"></div>
                                <!-- Winst balk (voorgrond) -->
                                <div style="height: {{ $maxRevenue > 0 ? max(0, ($data['profit'] / $maxRevenue) * 100) : 0 }}%" class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 absolute bottom-0 opacity-90 transition-all duration-700 shadow-lg shadow-indigo-200 rounded-t-sm"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-3 font-bold uppercase truncate w-full text-center">{{ $data['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Categorieën -->
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