<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParcelController extends Controller
{
    public function index()
    {
        $parcels = Parcel::where('user_id', Auth::id())
            ->withCount('items') // Telt automatisch hoeveel items erin zitten
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parcels.index', compact('parcels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parcel_no' => 'required|string|max:255',
            'tracking_code' => 'nullable|string|max:255',
            'shipping_cost' => 'nullable|numeric',
        ]);

        $parcel = new Parcel($validated);
        $parcel->user_id = Auth::id();
        $parcel->status = 'prep';
        $parcel->save();

        return redirect()->back()->with('success', 'Pakket aangemaakt!');
    }

    public function update(Request $request, Parcel $parcel)
    {
        if ($parcel->user_id !== Auth::id()) abort(403);
        
        $parcel->update($request->all());
        return redirect()->back()->with('success', 'Pakket geüpdatet');
    }

    public function destroy(Parcel $parcel)
    {
        if ($parcel->user_id !== Auth::id()) abort(403);
        
        // Zet items die in dit pakket zaten weer op parcel_id = null
        $parcel->items()->update(['parcel_id' => null]);
        $parcel->delete();
        
        return redirect()->back()->with('success', 'Pakket verwijderd');
    }
}