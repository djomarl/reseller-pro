<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemTemplate;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::where('user_id', Auth::id())->with('parcel');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('item_no', 'like', "%{$search}%");
            });
        }

        // View filter (archive vs active)
        if ($request->get('view') === 'archive') {
            $query->where('is_sold', true);
        } else {
            $query->where('is_sold', false);
        }

        $items = $query->latest()->paginate(20);
        $parcels = Parcel::where('user_id', Auth::id())->latest()->get();
        $templates = ItemTemplate::where('user_id', Auth::id())->get();

        return view('inventory.index', compact('items', 'parcels', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'brand' => 'nullable|string',
            'buy_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
            'parcel_id' => 'nullable|exists:parcels,id',
            'size' => 'nullable|string',
            'image_url' => 'nullable|string', // Base64 string
        ]);

        $item = new Item($validated);
        $item->user_id = Auth::id();
        
        // AI Auto-Fill als categorie leeg is
        if (empty($item->category)) {
            $analysis = $this->analyzeItemText($item->name);
            $item->category = $analysis['category'];
            if(empty($item->brand)) $item->brand = $analysis['brand'];
        }

        $item->save();

        return redirect()->back()->with('success', 'Item toegevoegd!');
    }

    public function update(Request $request, Item $item)
    {
        if ($item->user_id !== Auth::id()) abort(403);

        $item->fill($request->all());

        // Status logica
        if ($request->status == 'sold' || ($request->has('is_sold') && $request->is_sold)) {
            $item->status = 'sold';
            $item->is_sold = true;
            if (!$item->sold_date) $item->sold_date = now();
        } elseif ($request->status != 'sold') {
            $item->is_sold = false;
            $item->sold_date = null;
        }

        // AI Name Clean trigger (handmatig via knop)
        if ($request->has('clean_name')) {
            $analysis = $this->analyzeItemText($item->name);
            $item->name = $analysis['name'];
            $item->brand = $analysis['brand'];
            $item->category = $analysis['category'];
        }

        $item->save();
        return redirect()->back()->with('success', 'Item geüpdatet');
    }

    public function destroy(Item $item)
    {
        if ($item->user_id !== Auth::id()) abort(403);
        $item->delete();
        return redirect()->back()->with('success', 'Item verwijderd');
    }

    // --- DE SLIMME IMPORT FUNCTIE ---
    public function importText(Request $request)
    {
        $text = $request->input('import_text');
        $parcelId = $request->input('parcel_id');
        
        // Splits op "Item No" (met normale : of Chinese ：)
        $parts = preg_split('/Item\s*No[:：]/i', $text);
        $count = 0;

        foreach ($parts as $index => $part) {
            if ($index === 0) continue; // Header overslaan

            // 1. Haal Item No
            preg_match('/^([A-Z0-9]+)/i', trim($part), $noMatch);
            $itemNo = $noMatch[1] ?? '-';
            if (strlen($itemNo) < 3) continue;

            // 2. Haal Prijs (Zoek naar US $ ... of gelijkaardig)
            $price = 0;
            if (preg_match('/(?:US|EU|CNY|€|\$)\s*\\?[€$¥]?\s*(\d+[\.,]?\d*)/i', $part, $priceMatch)) {
                $price = floatval(str_replace(',', '.', $priceMatch[1]));
            }

            // 3. Haal Naam en maak schoon met AI
            $name = 'Imported Item';
            // Zoek tekst tussen "Shop Name" en de prijs/totalen
            if (preg_match('/Shop\s*Name[:：]\s*(.*?)(?=(US|EU|€|\$|Price|Total))/is', $part, $nameMatch)) {
                $rawName = trim($nameMatch[1]);
                // Verwijder de winkelnaam zelf als die erin staat (vaak kort)
                $rawName = preg_replace('/^\S+\s+/', '', $rawName); 
                
                $analysis = $this->analyzeItemText($rawName);
                $name = $analysis['name'];
                $brand = $analysis['brand'];
                $category = $analysis['category'];
            } else {
                // Fallback
                $analysis = $this->analyzeItemText("Item $itemNo");
                $brand = null;
                $category = 'Overige';
            }

            Item::create([
                'user_id' => Auth::id(),
                'parcel_id' => $parcelId,
                'item_no' => $itemNo,
                'name' => $name,
                'brand' => $brand,
                'category' => $category,
                'buy_price' => $price,
                'status' => 'todo'
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "$count items geïmporteerd & opgeschoond!");
    }

    // --- DE AI ENGINE ---
    private function analyzeItemText($rawText)
    {
        $text = strtolower($rawText);
        
        // 1. Merken Database
        $brands = [
            'nofaith' => 'No Faith Studios', 'no faith' => 'No Faith Studios',
            'nike' => 'Nike', 'jordan' => 'Jordan', 'stussy' => 'Stussy',
            'corteiz' => 'Corteiz', 'crtz' => 'Corteiz', 'trapstar' => 'Trapstar',
            'essentials' => 'Essentials', 'balenciaga' => 'Balenciaga',
            'stone island' => 'Stone Island', 'ralph' => 'Ralph Lauren',
            'arcteryx' => 'Arc\'teryx', 'yeezy' => 'Yeezy', 'supreme' => 'Supreme',
            'palm angels' => 'Palm Angels', 'off white' => 'Off-White',
            'carhartt' => 'Carhartt', 'diesel' => 'Diesel', 'gucci' => 'Gucci',
            'lv' => 'Louis Vuitton', 'prada' => 'Prada', 'dior' => 'Dior',
            'tib*erland' => 'Timberland', 'timberland' => 'Timberland'
        ];

        $foundBrand = null;
        foreach ($brands as $key => $niceName) {
            if (str_contains($text, $key)) {
                $foundBrand = $niceName;
                break;
            }
        }

        // 2. Categorie Detectie
        $categories = [
            'Truien/Hoodies' => ['hoodie', 'sweater', 'trui', 'zip', 'fleece', 'vest'],
            'Broeken' => ['jeans', 'pant', 'broek', 'jogger', 'short', 'denim', 'trousers'],
            'Schoenen' => ['shoe', 'sneaker', 'boot', 'dunk', 'jordan 4', 'yeezy', 'slide', 'slipper'],
            'T-Shirts' => ['tee', 'shirt', 'top', 'polo'],
            'Jassen' => ['jacket', 'coat', 'windbreaker', 'puffer', 'varsity'],
            'Hoofddeksels' => ['cap', 'hat', 'beanie', 'muts'],
            'Accessoires' => ['bag', 'tas', 'belt', 'riem', 'sock', 'wallet']
        ];

        $foundCategory = 'Overige';
        $foundType = '';
        
        foreach ($categories as $cat => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $foundCategory = $cat;
                    $foundType = ucfirst($keyword); // Bewaar type voor naam (bijv. 'Jeans')
                    break 2;
                }
            }
        }

        // 3. Naam Generatie
        $cleanName = $rawText;
        if ($foundBrand) {
            $cleanName = $foundBrand;
            // Voeg details toe
            if (str_contains($text, 'bootcut')) $cleanName .= ' Bootcut';
            if (str_contains($text, 'flared')) $cleanName .= ' Flared';
            if (str_contains($text, 'zip')) $cleanName .= ' Zip';
            if (str_contains($text, 'cargo')) $cleanName .= ' Cargo';
            
            if ($foundType && !str_contains(strtolower($cleanName), strtolower($foundType))) {
                $cleanName .= " $foundType";
            }
        } else {
            // Als geen merk gevonden, maak schoon door rare tekens te verwijderen
            $cleanName = preg_replace('/[^\w\s]/', '', $rawText);
            // Pak eerste 4 woorden
            $words = explode(' ', $cleanName);
            $cleanName = implode(' ', array_slice($words, 0, 4));
        }

        return [
            'name' => trim($cleanName),
            'brand' => $foundBrand,
            'category' => $foundCategory
        ];
    }
}