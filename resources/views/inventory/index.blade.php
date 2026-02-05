<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Voorraad') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Knoppenbalk -->
            <div class="flex justify-between mb-4">
                <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-2">
                    <input type="text" name="search" placeholder="Zoek item..." value="{{ request('search') }}" class="border-gray-300 rounded-md shadow-sm">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Zoek</button>
                </form>

                <button @click="showModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold hover:bg-indigo-700">
                    + Nieuw Item
                </button>
            </div>

            <!-- Tabel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="pb-2">Naam</th>
                                <th class="pb-2">Merk</th>
                                <th class="pb-2 text-right">Prijs</th>
                                <th class="pb-2">Pakket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr class="border-b last:border-0 hover:bg-gray-50">
                                    <td class="py-3 font-bold">{{ $item->name }}</td>
                                    <td class="py-3 text-gray-500">{{ $item->brand ?? '-' }}</td>
                                    <td class="py-3 text-right">€ {{ number_format($item->buy_price, 2) }}</td>
                                    <td class="py-3">
                                        @if($item->parcel)
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $item->parcel->parcel_no }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $items->links() }} 
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL (Pop-up) -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-transition>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6" @click.away="showModal = false">
                <h3 class="text-lg font-bold mb-4">Nieuw Item</h3>
                
                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Naam</label>
                        <input type="text" name="name" class="w-full border-gray-300 rounded-md" required>
                    </div>
                    <div class="flex gap-4 mb-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-bold mb-1">Merk</label>
                            <input type="text" name="brand" class="w-full border-gray-300 rounded-md">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-bold mb-1">Prijs (€)</label>
                            <input type="number" step="0.01" name="buy_price" class="w-full border-gray-300 rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showModal = false" class="text-gray-500 px-4 py-2 hover:text-gray-700">Annuleren</button>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Opslaan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>