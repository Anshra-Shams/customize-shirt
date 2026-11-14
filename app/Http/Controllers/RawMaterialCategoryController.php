<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RawMaterialCategoryController extends Controller
{
    /**
     * Display a listing of raw material categories.
     */
    public function index(Request $request)
    {
        $query = Category::rawMaterials()->withCount('rawMaterialProducts');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('name', 'like', "%{$s}%");
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total_categories' => Category::rawMaterials()->count(),
            'total_materials' => \App\Models\Product::where(function($q) {
                $q->where('is_raw_material', 1)->orWhere('is_part', 1);
            })->whereNotNull('category_id')->count(),
        ];

        return view('admin_panel.raw_material_categories.index', compact('categories', 'stats'));
    }

    /**
     * Store a newly created raw material category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->where(function ($query) {
                    return $query->where('is_raw_material', 1);
                }),
            ],
        ]);

        $category = Category::create([
            'name' => trim($validated['name']),
            'is_raw_material' => 1,
            'show_on_website' => 0,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Raw material category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('raw_material_categories.index')->with('success', 'Category added successfully.');
    }

    /**
     * Update the specified raw material category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::rawMaterials()->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where(function ($query) {
                        return $query->where('is_raw_material', 1);
                    })
                    ->ignore($id),
            ],
        ]);

        $category->update([
            'name' => trim($validated['name']),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('raw_material_categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified raw material category.
     */
    public function destroy($id)
    {
        $category = Category::rawMaterials()->withCount('rawMaterialProducts')->findOrFail($id);

        if ($category->raw_material_products_count > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because {$category->raw_material_products_count} raw materials are assigned to it.");
        }

        $category->delete();

        return redirect()->route('raw_material_categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Return JSON of all raw material categories (for dropdowns).
     */
    public function getCategoriesJson()
    {
        $categories = Category::rawMaterials()->orderBy('name')->get(['id', 'name']);
        return response()->json($categories);
    }
}
