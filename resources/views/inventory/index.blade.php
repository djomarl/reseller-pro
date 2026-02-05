<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showImport: false, showNew: false }">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                <strong class="font-bold">Succes!</strong> <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Toolbar -->
        <div class="bg-white/80 backdrop-blur-md p-4 rounded-3xl border border-white shadow-sm mb-8 flex flex-col md:flex-row justify-between gap-4">
            <div class="flex gap-3">
                <form action="{{ route('inventory.index') }}" method="GET" class="relative group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Zoeken..." class="pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 w-64 shadow-sm group-hover:border-slate-300 transition">
                    <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>
                
                <a href="{{ route('inventory.index', ['view' => request('view') == 'archive' ? 'active' : 'archive']) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 flex items-center gap-2 transition shadow-sm">
                    @if(request('view') == 'archive')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> Toon Voorraad
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg> Archief
                    @endif
                </a>
            </div>

            <div class="flex gap-3">
                <button @click="showImport = true" class="px-5 py-2.5 bg-white border border-indigo-100 text-indigo-600 rounded-xl text-sm font-bold shadow-sm hover:bg-indigo-50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Import Text
                </button>
                <button @click="showNew = true" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold shadow-lg hover:bg-slate-800 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nieuw Item
                </button>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50/80 backdrop-blur border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Merk / Categorie</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Inkoop</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Verkoop</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-xl border border-slate-200 flex-shrink-0 overflow-hidden shadow-sm">
                                        @if($item->image_url)
                                            <img src="{{ $item->image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 flex items-center gap-2">
                                            {{ $item->name }}
                                            <form action="{{ route('inventory.update', $item) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="clean_name" value="1">
                                                <button type="submit" class="text-slate-300 hover:text-purple-500 transition opacity-0 group-hover:opacity-100" title="AI Clean">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-mono">{{ $item->item_no }}</span>
                                            @if($item->parcel)
                                                <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded border border-indigo-100 flex items-center gap-1">
                                                    📦 {{ $item->parcel->parcel_no }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700">{{ $item->brand ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $item->category }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-600">
                                € {{ number_format($item->buy_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('inventory.update', $item) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="number" step="0.01" name="sell_price" value="{{ $item->sell_price }}" class="w-20 text-right bg-transparent border-none p-0 font-bold focus:ring-0 focus:bg-white rounded transition text-slate-800 placeholder-slate-300" placeholder="-">
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('inventory.update', $item) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold uppercase rounded-full px-3 py-1 border-none cursor-pointer shadow-sm transition {{ $item->status == 'sold' ? 'bg-emerald-100 text-emerald-700' : ($item->status == 'online' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
                                        <option value="todo" {{ $item->status == 'todo' ? 'selected' : '' }}>To-do</option>
                                        <option value="online" {{ $item->status == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="sold" {{ $item->status == 'sold' ? 'selected' : '' }}>Verkocht</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('inventory.destroy', $item) }}" method="POST" onsubmit="return confirm('Verwijderen?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-12 text-center text-slate-400 italic">Geen items gevonden. Importeer iets!</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-100">{{ $items->links() }}</div>
        </div>

        <!-- IMPORT MODAL -->
        <div x-show="showImport" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showImport = false" x-transition>
            <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl m-4 border border-white/50">
                <h3 class="font-heading font-bold text-xl mb-2 flex items-center gap-2">
                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg></div>
                    Import Text
                </h3>
                <p class="text-sm text-slate-500 mb-6">Kopieer de tekst van je order (Ctrl+A, Ctrl+C) en plak het hieronder. De AI doet de rest.</p>
                
                <form action="{{ route('inventory.import') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="text-xs font-bold text-slate-500 uppercase">Koppel aan Pakket</label>
                        <select name="parcel_id" class="w-full p-3 rounded-xl border border-slate-200 mt-1 bg-slate-50 focus:bg-white transition">
                            <option value="">Geen</option>
                            @foreach($parcels as $parcel)
                                <option value="{{ $parcel->id }}">{{ $parcel->parcel_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="import_text" class="w-full h-64 p-4 rounded-xl border border-slate-200 font-mono text-xs bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition" placeholder="Plak hier je tekst... Order No: DO..."></textarea>
                    
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showImport = false" class="text-slate-500 hover:text-slate-700 font-medium">Annuleren</button>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Importeren 🚀</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- NEW ITEM MODAL -->
        <div x-show="showNew" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showNew = false" x-transition>
            <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-lg m-4">
                <h3 class="font-heading font-bold text-xl mb-6">Nieuw Item</h3>
                <form action="{{ route('inventory.store') }}" method="POST" x-data="{ 
                    updateFromTemplate(event) {
                        const id = event.target.value;
                        const tpls = {{ Js::from($templates) }};
                        const tpl = tpls.find(t => t.id == id);
                        if(tpl) {
                            this.$refs.name.value = tpl.name;
                            this.$refs.brand.value = tpl.brand;
                            this.$refs.price.value = tpl.default_buy_price;
                            this.$refs.img.value = tpl.image_url;
                        }
                    }
                }">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <select @change="updateFromTemplate($event)" class="w-full p-2.5 rounded-xl border-slate-200 text-sm font-bold text-slate-600 bg-slate-50">
                                <option value="">✨ Kies een Preset (Optioneel)</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div><label class="text-xs font-bold uppercase text-slate-400">Naam</label><input x-ref="name" type="text" name="name" required class="w-full p-3 rounded-xl border-slate-200 mt-1"></div>
                        <div class="flex gap-4">
                            <div class="flex-1"><label class="text-xs font-bold uppercase text-slate-400">Merk</label><input x-ref="brand" type="text" name="brand" class="w-full p-3 rounded-xl border-slate-200 mt-1"></div>
                            <div class="w-24"><label class="text-xs font-bold uppercase text-slate-400">Maat</label><input type="text" name="size" class="w-full p-3 rounded-xl border-slate-200 mt-1"></div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="text-xs font-bold uppercase text-slate-400">Inkoop</label>
                                <div class="relative mt-1"><span class="absolute left-3 top-3 text-slate-400 text-xs">€</span><input x-ref="price" type="number" step="0.01" name="buy_price" class="w-full pl-8 p-3 rounded-xl border-slate-200"></div>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-bold uppercase text-slate-400">Pakket</label>
                                <select name="parcel_id" class="w-full p-3 rounded-xl border-slate-200 mt-1 bg-white">
                                    <option value="">Geen</option>
                                    @foreach($parcels as $p)<option value="{{ $p->id }}">{{ $p->parcel_no }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        
                        <input type="hidden" name="image_url" x-ref="img">

                        <button class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-slate-800 transition mt-2">Toevoegen</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>