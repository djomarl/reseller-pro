<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Parcel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Data ophalen
        $soldItems = Item::where('user_id', $userId)->where('is_sold', true)->with('parcel')->get();
        $unsoldItems = Item::where('user_id', $userId)->where('is_sold', false)->with('parcel')->get();
        $parcels = Parcel::where('user_id', $userId)->get();

        $soldItemsWithPrice = $soldItems->filter(function ($item) {
            return !is_null($item->sell_price) && $item->sell_price > 0;
        });

        // 1. Financiële Stats
        $totalBuyCost = Item::where('user_id', $userId)->sum('buy_price');
        $totalShipping = $parcels->sum('shipping_cost');
        $totalInvested = $totalBuyCost + $totalShipping;
        $totalRevenue = $soldItemsWithPrice->sum('sell_price');
        
        // Netto resultaat (gerealiseerd) op basis van totale investering
        // Zo verandert dit ook wanneer je nieuwe parcels of kosten toevoegt.
        $realizedProfit = $totalRevenue - $totalInvested;

        // Potentieel
        $potentialRevenue = $unsoldItems->whereNotNull('sell_price')->sum('sell_price'); 
        $potentialProfit = ($totalRevenue + $potentialRevenue) - $totalInvested;

        // Break even percentage
        $breakEvenPercent = $totalInvested > 0 ? min(100, ($totalRevenue / $totalInvested) * 100) : 0;

        // 2. Operationele Stats
        $itemsSold = $soldItems->count();
        $itemsInStock = $unsoldItems->count();
        $totalParcels = $parcels->count();

        // Gemiddelde verkoopsnelheid
        $totalDays = 0;
        $countForDays = 0;
        foreach($soldItems as $item) {
            $created = Carbon::parse($item->created_at);
            $sold = $item->sold_date ? Carbon::parse($item->sold_date) : Carbon::parse($item->updated_at);
            $totalDays += $created->diffInDays($sold);
            $countForDays++;
        }
        $avgSellDays = $countForDays > 0 ? round($totalDays / $countForDays) : 0;

        // Winkeldochters (> 30 dagen op voorraad)
        $oldStockCount = 0;
        foreach($unsoldItems as $item) {
            if(Carbon::parse($item->created_at)->diffInDays(now()) > 30) {
                $oldStockCount++;
            }
        }

        // 3. Top Categorieën
        $categories = [];
        foreach($soldItemsWithPrice as $item) {
            $cat = $item->category ?: 'Overige';
            if(!isset($categories[$cat])) {
                $categories[$cat] = ['name' => $cat, 'sold' => 0, 'profit' => 0];
            }
            
            $shippingShare = 0;
            if ($item->parcel && $item->parcel->items()->count() > 0) {
                $shippingShare = $item->parcel->shipping_cost / $item->parcel->items()->count();
            }
            
            $profit = $item->sell_price - $item->buy_price - $shippingShare;
            
            $categories[$cat]['sold']++;
            $categories[$cat]['profit'] += $profit;
        }
        usort($categories, fn($a, $b) => $b['sold'] <=> $a['sold']);
        $topCategories = array_slice($categories, 0, 5);

        // 4. Chart Data (Laatste 6 maanden)
        $chartData = [];
        $maxRevenue = 0;
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $label = $date->format('M');
            
            $monthItems = $soldItemsWithPrice->filter(function($item) use ($monthKey) {
                $dateCheck = $item->sold_date ? Carbon::parse($item->sold_date) : Carbon::parse($item->updated_at);
                return $dateCheck->format('Y-m') === $monthKey;
            });

            $revenue = $monthItems->sum('sell_price');
            $cost = $monthItems->sum(function ($item) {
                $shippingShare = 0;
                if ($item->parcel && $item->parcel->items()->count() > 0) {
                    $shippingShare = $item->parcel->shipping_cost / $item->parcel->items()->count();
                }
                return $item->buy_price + $shippingShare;
            });
            
            if($revenue > $maxRevenue) $maxRevenue = $revenue;

            $chartData[] = [
                'label' => $label,
                'revenue' => $revenue,
                'profit' => $revenue - $cost
            ];
        }

        return view('dashboard', compact(
            'totalInvested', 'totalRevenue', 'realizedProfit', 'potentialProfit', 'breakEvenPercent',
            'itemsSold', 'itemsInStock', 'totalParcels', 'avgSellDays', 'oldStockCount',
            'topCategories', 'chartData', 'maxRevenue'
        ));
    }
}