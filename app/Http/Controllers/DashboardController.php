<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $soldItems = Item::where('user_id', $userId)->where('is_sold', true)->get();
        $unsoldItems = Item::where('user_id', $userId)->where('is_sold', false)->get();
        $parcels = Parcel::where('user_id', $userId)->get();

        // 1. Financiële Stats
        $totalBuyCost = Item::where('user_id', $userId)->sum('buy_price');
        $totalShipping = $parcels->sum('shipping_cost');
        $totalInvested = $totalBuyCost + $totalShipping;
        
        $totalRevenue = $soldItems->sum('sell_price');
        
        // Winst berekening (simpel)
        $realizedProfit = 0;
        foreach($soldItems as $item) {
            $shippingShare = 0;
            if ($item->parcel) {
                // Voorkom delen door nul
                $count = $item->parcel->items()->count();
                if($count > 0) $shippingShare = $item->parcel->shipping_cost / $count;
            }
            $realizedProfit += ($item->sell_price - $item->buy_price - $shippingShare);
        }

        $potentialProfit = ($unsoldItems->sum('sell_price') + $totalRevenue) - $totalInvested;

        // 2. Chart Data (Laatste 6 maanden)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            
            $monthItems = $soldItems->filter(function($item) use ($date) {
                return $item->sold_date && $item->sold_date->format('Y-m') === $date->format('Y-m');
            });

            $revenue = $monthItems->sum('sell_price');
            $cost = $monthItems->sum('buy_price'); // Excl shipping voor simpele chart
            
            $chartData[] = [
                'label' => $monthName,
                'revenue' => $revenue,
                'profit' => $revenue - $cost
            ];
        }

        return view('dashboard', compact(
            'totalInvested', 'totalRevenue', 'realizedProfit', 
            'soldItems', 'unsoldItems', 'parcels', 'chartData'
        ));
    }
}