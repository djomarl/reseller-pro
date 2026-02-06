<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Parcel;
use App\Models\ItemTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Item::where('user_id', $userId);

        // 1. View Toggle: Archive vs Active
        // view='archive' -> Alleen verkocht. Anders -> Alleen voorraad.
        $view = $request->get('view', 'active');
        
        if ($view === 'archive') {
            $query->where('is_sold', true);
        } else {
            $query->where('is_sold', false);
        }

        // 2. Zoeken (Naam, Merk, Item #)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('item_no', 'like', "%{$search}%");
            });
        }

        // 3. Filteren op Categorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // 4. Filteren op Merk
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // 5. Filteren op Status
        if ($request->filled('status')) {
             $query->where('status', $request->status);
        }

        // Data ophalen (met paginering voor de cards view)
        $items = $query->latest()->paginate(24)->withQueryString();

        // Data voor de filters en dropdowns
        $categories = Item::where('user_id', $userId)->whereNotNull('category')->distinct()->pluck('category')->sort();
        $brands = Item::where('user_id', $userId)->whereNotNull('brand')->distinct()->pluck('brand')->sort();
        $parcels = Parcel::where('user_id', $userId)->latest()->get();
        $templates = ItemTemplate::where('user_id', $userId)->get();

        return view('inventory.index', compact('items', 'categories', 'brands', 'parcels', 'templates', 'view'));
    }

    public function create()
    {
        $parcels = Parcel::where('user_id', Auth::id())->latest()->get();
        return view('inventory.create', compact('parcels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'brand' => 'nullable|string',
            'category' => 'nullable|string', 
            'buy_price' => 'nullable|numeric',
            'sell_price' => 'nullable|numeric',
            'parcel_id' => 'nullable|exists:parcels,id',
            'size' => 'nullable|string',
            'image_url' => 'nullable|string',
        ]);

        $item = new Item($validated);
        $item->user_id = Auth::id();
        $item->status = 'todo'; // Standaard status
        $item->is_sold = false;
        
        // AI Auto-Fill als categorie leeg is
        if (empty($item->category)) {
            $analysis = $this->analyzeItemText($item->name);
            $item->category = $analysis['category'];
            
            if(empty($item->brand)) {
                $item->brand = $analysis['brand'];
            }
        }

        $item->save();

        return redirect()->back()->with('success', 'Item toegevoegd!');
    }

    // LET OP: Variabele naam gewijzigd naar $inventory om Route Model Binding te fixen
    public function edit(Item $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403);
        }
        
        // We sturen hem als 'item' naar de view, want je view verwacht $item
        $item = $inventory;
        $parcels = Parcel::where('user_id', Auth::id())->latest()->get();
        $templates = ItemTemplate::where('user_id', Auth::id())->get();
        
        return view('inventory.edit', compact('item', 'parcels', 'templates'));
    }

    // LET OP: Variabele naam gewijzigd naar $inventory
    public function update(Request $request, Item $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'image' => 'nullable|image|max:4096',
        ]);

        $inventory->fill($request->all());

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('items', 'public');
            $inventory->image_url = Storage::url($path);
        }

        // Status logica: Synchroniseer status met is_sold
        if ($request->status == 'sold') {
            $inventory->is_sold = true;
            if (!$inventory->sold_date) $inventory->sold_date = now();
        } else {
            // Als je hem terugzet naar 'online' of 'todo', is hij niet meer verkocht
            // Tenzij we in de archief view zitten, maar meestal wil je dit resetten.
            $inventory->is_sold = false;
            $inventory->sold_date = null;
        }

        // AI Name Clean trigger (handmatig via knop in edit view)
        if ($request->has('clean_name')) {
            $analysis = $this->analyzeItemText($inventory->name);
            $inventory->name = $analysis['name'];
            $inventory->brand = $analysis['brand'];
            $inventory->category = $analysis['category'];
        }

        $inventory->save();
        return redirect()->route('inventory.index')->with('success', 'Item bijgewerkt');
    }

    // LET OP: Variabele naam gewijzigd naar $inventory
    public function destroy(Item $inventory)
    {
        if ($inventory->user_id !== Auth::id()) {
            abort(403);
        }
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Item verwijderd');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,set_status,set_parcel',
            'items' => 'required|array',
            'items.*' => 'exists:items,id',
            'status' => 'nullable|string',
            'parcel_id' => 'nullable|exists:parcels,id',
        ]);

        $items = Item::whereIn('id', $request->items)
                    ->where('user_id', Auth::id())
                    ->get();

        $count = 0;
        foreach ($items as $item) {
            switch ($request->action) {
                case 'delete':
                    $item->delete();
                    $count++;
                    break;
                case 'set_status':
                    $item->status = $request->status;
                    if ($request->status == 'sold') {
                        $item->is_sold = true;
                        $item->sold_date = $item->sold_date ?? now();
                    } else {
                        $item->is_sold = false;
                        $item->sold_date = null;
                    }
                    $item->save();
                    $count++;
                    break;
                case 'set_parcel':
                    $item->parcel_id = $request->parcel_id;
                    $item->save();
                    $count++;
                    break;
            }
        }

        return redirect()->back()->with('success', "$count items bijgewerkt!");
    }

    // --- IMPORT LOGICA ---
    public function importText(Request $request)
    {
        $text = $request->input('import_text');
        $parcelId = $request->input('parcel_id');
        
        $parts = preg_split('/Item\s*No[:：]/i', $text);
        $count = 0;

        foreach ($parts as $index => $part) {
            if ($index === 0) continue;

            preg_match('/^([A-Z0-9]+)/i', trim($part), $noMatch);
            $itemNo = $noMatch[1] ?? '-';
            if (strlen($itemNo) < 3) continue;

            $price = 0;
            if (preg_match('/(?:US|EU|CNY|€|\$)\s*\\?[€$¥]?\s*(\d+[\.,]?\d*)/i', $part, $priceMatch)) {
                $price = floatval(str_replace(',', '.', $priceMatch[1]));
            }

            $name = 'Imported Item';
            if (preg_match('/Shop\s*Name[:：]\s*(.*?)(?=(US|EU|€|\$|Price|Total))/is', $part, $nameMatch)) {
                $rawName = trim($nameMatch[1]);
                $rawName = preg_replace('/^\S+\s+/', '', $rawName); 
                
                $analysis = $this->analyzeItemText($rawName);
                $name = $analysis['name'];
                $brand = $analysis['brand'];
                $category = $analysis['category'];
            } else {
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
                'is_sold' => false,
                'status' => 'stock'
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "$count items geïmporteerd & opgeschoond!");
    }

    // --- AI ANALYSE LOGICA ---
    private function analyzeItemText($rawText)
    {
        $text = strtolower($rawText);
        
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
                    $foundType = ucfirst($keyword);
                    break 2;
                }
            }
        }

        $cleanName = $rawText;
        if ($foundBrand) {
            $cleanName = $foundBrand;
            if (str_contains($text, 'bootcut')) $cleanName .= ' Bootcut';
            if (str_contains($text, 'flared')) $cleanName .= ' Flared';
            if (str_contains($text, 'zip')) $cleanName .= ' Zip';
            if (str_contains($text, 'cargo')) $cleanName .= ' Cargo';
            
            if ($foundType && !str_contains(strtolower($cleanName), strtolower($foundType))) {
                $cleanName .= " $foundType";
            }
        } else {
            $cleanName = preg_replace('/[^\w\s]/', '', $rawText);
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