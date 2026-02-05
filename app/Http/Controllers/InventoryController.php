<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Haal items op van de ingelogde gebruiker
        $query = Item::where('user_id', Auth::id())->with('parcel');

        // Zoekbalk logica
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(20);
        $parcels = Parcel::where('user_id', Auth::id())->get();

        // Stuur data naar de 'inventory.index' view (die maken we zo)
        return view('inventory.index', compact('items', 'parcels'));
    }

    public function store(Request $request)
    {
        // Valideer input
        $validated = $request->validate([
            'name' => 'required|string',
            'brand' => 'nullable|string',
            'buy_price' => 'required|numeric',
            'parcel_id' => 'nullable|exists:parcels,id',
        ]);

        // Opslaan & koppelen aan user
        $item = new Item($validated);
        $item->user_id = Auth::id();
        $item->category = 'Overige'; // Simpele default
        $item->save();

        return redirect()->back()->with('success', 'Item toegevoegd!');
    }
}