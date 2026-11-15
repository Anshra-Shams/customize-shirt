<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of raw materials with stocks and filters.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category_relation', 'unit', 'warehouseStocks.warehouse'])
            ->withSum('warehouseStocks', 'total_pieces')
            ->where(function ($q) {
                $q->where('is_raw_material', 1)
                  ->orWhere('is_part', 1);
            });

        // Search filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                  ->orWhere('item_code', 'like', "%{$s}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->having('warehouse_stocks_sum_total_pieces', '<=', 0);
            } elseif ($request->stock_status === 'in_stock') {
                $query->having('warehouse_stocks_sum_total_pieces', '>', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->whereNotNull('alert_quantity')
                      ->havingRaw('COALESCE(warehouse_stocks_sum_total_pieces, 0) <= products.alert_quantity');
            }
        }

        $rawMaterials = $query->orderBy('item_name')->paginate(20)->withQueryString();

        // Summary statistics
        $allRawQuery = Product::where(function ($q) {
            $q->where('is_raw_material', 1)
              ->orWhere('is_part', 1);
        })->withSum('warehouseStocks', 'total_pieces')->get();

        $stats = [
            'total' => $allRawQuery->count(),
            'in_stock' => $allRawQuery->filter(fn($item) => ($item->warehouse_stocks_sum_total_pieces ?? 0) > 0)->count(),
            'out_of_stock' => $allRawQuery->filter(fn($item) => ($item->warehouse_stocks_sum_total_pieces ?? 0) <= 0)->count(),
            'low_stock' => $allRawQuery->filter(function($item) {
                $stock = (float) ($item->warehouse_stocks_sum_total_pieces ?? 0);
                return !is_null($item->alert_quantity) && $stock <= (float)$item->alert_quantity;
            })->count(),
        ];

        // Only fetch Raw Material Categories
        $categories = Category::rawMaterials()->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $defaultNames = ['Fabrics', 'Buttons', 'Threads', 'Zippers', 'Collars & Cuffs', 'Packaging', 'Labels & Tags'];
            foreach ($defaultNames as $dName) {
                Category::firstOrCreate(
                    ['name' => $dName, 'is_raw_material' => 1],
                    ['show_on_website' => 0]
                );
            }
            $categories = Category::rawMaterials()->orderBy('name')->get();
        }

        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('warehouse_name')->get();

        // Suggest next code: RM-0001
        $lastProduct = Product::where('item_code', 'like', 'RM-%')->orderByDesc('id')->first();
        $nextNum = 1;
        if ($lastProduct && preg_match('/RM-(\d+)/', $lastProduct->item_code, $matches)) {
            $nextNum = ((int)$matches[1]) + 1;
        }
        $nextCode = 'RM-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('admin_panel.raw_materials.index', compact('rawMaterials', 'stats', 'categories', 'units', 'nextCode', 'warehouses'));
    }

    /**
     * Show the form for creating a new raw material (redirects to modal on index).
     */
    public function create()
    {
        return redirect()->route('raw_materials.index', ['open_modal' => 1]);
    }

    /**
     * Store a newly created raw material.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:100|unique:products,item_code',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            // Auto generate code if empty
            $code = $validated['item_code'] ?? null;
            if (empty($code)) {
                $lastProduct = Product::where('item_code', 'like', 'RM-%')->orderByDesc('id')->first();
                $nextNum = 1;
                if ($lastProduct && preg_match('/RM-(\d+)/', $lastProduct->item_code, $matches)) {
                    $nextNum = ((int)$matches[1]) + 1;
                }
                $code = 'RM-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            $isActive = $request->has('is_active') ? ($request->is_active ? 1 : 0) : 1;

            $product = Product::create([
                'creater_id' => Auth::id() ?? 1,
                'category_id' => $validated['category_id'] ?? null,
                'unit_id' => $validated['unit_id'] ?? null,
                'item_code' => $code,
                'item_name' => $validated['item_name'],
                'purchase_price_per_piece' => $validated['purchase_price'],
                'purchase_price_per_box' => $validated['purchase_price'],
                'sale_price_per_piece' => $validated['purchase_price'],
                'pieces_per_box' => 1,
                'total_m2' => 0,
                'size_mode' => 'by_pieces',
                'alert_quantity' => $validated['alert_quantity'] ?? 0,
                'is_raw_material' => 1,
                'is_part' => 1,
                'is_active' => $isActive,
                'meta_description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Raw material added successfully.',
                    'raw_material' => $product->load('unit', 'category_relation'),
                ]);
            }

            return redirect()->route('raw_materials.index')->with('success', 'Raw material added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Error creating raw material: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a raw material.
     */
    public function edit($id)
    {
        $rawMaterial = Product::where(function ($q) {
            $q->where('is_raw_material', 1)
              ->orWhere('is_part', 1);
        })->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $rawMaterial->id,
                    'item_name' => $rawMaterial->item_name,
                    'item_code' => $rawMaterial->item_code,
                    'category_id' => $rawMaterial->category_id,
                    'unit_id' => $rawMaterial->unit_id,
                    'purchase_price' => (float)$rawMaterial->purchase_price_per_piece,
                    'alert_quantity' => (float)$rawMaterial->alert_quantity,
                    'description' => $rawMaterial->meta_description,
                    'is_active' => (bool)$rawMaterial->is_active,
                ]
            ]);
        }

        // Only fetch Raw Material Categories
        $categories = Category::rawMaterials()->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $defaultNames = ['Fabrics', 'Buttons', 'Threads', 'Zippers', 'Collars & Cuffs', 'Packaging', 'Labels & Tags'];
            foreach ($defaultNames as $dName) {
                Category::firstOrCreate(
                    ['name' => $dName, 'is_raw_material' => 1],
                    ['show_on_website' => 0]
                );
            }
            $categories = Category::rawMaterials()->orderBy('name')->get();
        }

        $units = Unit::orderBy('name')->get();

        return view('admin_panel.raw_materials.edit', compact('rawMaterial', 'categories', 'units'));
    }

    /**
     * Update the raw material.
     */
    public function update(Request $request, $id)
    {
        $rawMaterial = Product::where(function ($q) {
            $q->where('is_raw_material', 1)
              ->orWhere('is_part', 1);
        })->findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:products,item_code,' . $id,
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable',
        ]);

        $isActive = $request->has('is_active') ? ($request->is_active ? 1 : 0) : 1;

        $rawMaterial->update([
            'item_name' => $validated['item_name'],
            'item_code' => $validated['item_code'],
            'category_id' => $validated['category_id'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'purchase_price_per_piece' => $validated['purchase_price'],
            'purchase_price_per_box' => $validated['purchase_price'],
            'alert_quantity' => $validated['alert_quantity'] ?? 0,
            'is_active' => $isActive,
            'meta_description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('raw_materials.index')->with('success', 'Raw material updated successfully.');
    }

    /**
     * Delete (soft-delete) raw material.
     */
    public function destroy($id)
    {
        $rawMaterial = Product::where(function ($q) {
            $q->where('is_raw_material', 1)
              ->orWhere('is_part', 1);
        })->findOrFail($id);

        $rawMaterial->delete();

        return redirect()->route('raw_materials.index')->with('success', 'Raw material deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive($id)
    {
        $rawMaterial = Product::where(function ($q) {
            $q->where('is_raw_material', 1)
              ->orWhere('is_part', 1);
        })->findOrFail($id);

        $rawMaterial->is_active = !$rawMaterial->is_active;
        $rawMaterial->save();

        $statusMsg = $rawMaterial->is_active ? 'Raw material activated successfully.' : 'Raw material deactivated successfully.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool)$rawMaterial->is_active,
                'message' => $statusMsg,
            ]);
        }

        return redirect()->back()->with('success', $statusMsg);
    }

    /**
     * AJAX Search for Raw Materials (used by Purchase, Orders, POS).
     */
    public function ajaxSearch(Request $request)
    {
        $term = trim($request->get('q') ?? $request->get('term') ?? '');

        $query = Product::with(['unit', 'category_relation'])
            ->withSum('warehouseStocks', 'total_pieces')
            ->where(function ($q) {
                $q->where('is_raw_material', 1)
                  ->orWhere('is_part', 1);
            })
            ->where('is_active', true);

        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('item_name', 'like', "%{$term}%")
                  ->orWhere('item_code', 'like', "%{$term}%");
            });
        }

        $items = $query->limit(30)->get();

        $results = $items->map(function ($item) {
            $stock = (float)($item->warehouse_stocks_sum_total_pieces ?? 0);
            $unit = $item->unit->name ?? 'Pcs';
            return [
                'id' => $item->id,
                'text' => "{$item->item_name} ({$item->item_code}) - Stock: {$stock} {$unit}",
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'unit' => $unit,
                'stock' => $stock,
                'price' => (float)$item->purchase_price_per_piece,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
