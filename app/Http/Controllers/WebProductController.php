<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class WebProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('active', true);

        if ($request->filled('category')) {
            $cat = $request->input('category');
            $query->where(function($q) use ($cat) {
                if (Schema::hasColumn('products', 'product_category')) {
                    $q->orWhere('product_category', $cat);
                }
                if (Schema::hasColumn('products', 'productCategory')) {
                    $q->orWhere('productCategory', $cat);
                }
            });
        }

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
                if (Schema::hasColumn('products', 'chemicalFormula')) {
                    $sub->orWhere('chemicalFormula', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('products', 'symbol')) {
                    $sub->orWhere('symbol', 'like', "%{$search}%");
                }
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $products = $query->orderBy('name')->paginate($perPage)->withQueryString();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        return view('products.create', compact('product'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.create', compact('product'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'productCategory' => 'nullable|string',
            'product_category' => 'nullable|string',
            'category' => 'nullable|string',
            'chemicalFormula' => 'nullable|string',
            'symbol' => 'nullable|string',
            'code' => 'nullable|string|max:64',
            'hsCode' => 'nullable|string',
            'hs_code' => 'nullable|string',
            'basel_code' => 'nullable|string',
            'base_code_annexix' => 'nullable|string',
            'unitOfMeasurement' => 'nullable|string',
            'uom' => 'nullable|string',
            'ec_waste_code' => 'nullable|string',
            'eu_list_of_wastes' => 'nullable|string',
            'description' => 'nullable|string',
            'itemSpecification' => 'nullable|string',
            'item_specification' => 'nullable|string',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? $validated['productCategory'] ?? $validated['product_category'] ?? 'COMMODITIES',
            'chemicalFormula' => $validated['chemicalFormula'] ?? $validated['symbol'] ?? null,
            'code' => $validated['code'] ?? null,
            'hsCode' => $validated['hsCode'] ?? $validated['hs_code'] ?? null,
            'basel_code' => $validated['basel_code'] ?? $validated['base_code_annexix'] ?? null,
            'unitOfMeasurement' => $validated['unitOfMeasurement'] ?? $validated['uom'] ?? 'MT',
            'ec_waste_code' => $validated['ec_waste_code'] ?? $validated['eu_list_of_wastes'] ?? null,
            'description' => $validated['description'] ?? $validated['itemSpecification'] ?? $validated['item_specification'] ?? null,
            'active' => $request->has('active') ? $request->boolean('active') : true,
        ]);

        return redirect()->route('products.show', $product->id)->with('success', 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $data = [];
        if ($request->has('name')) $data['name'] = $request->input('name');
        if ($request->has('productCategory') || $request->has('category') || $request->has('product_category')) {
            $data['category'] = $request->input('category', $request->input('productCategory', $request->input('product_category')));
        }
        if ($request->has('chemicalFormula') || $request->has('symbol')) {
            $data['chemicalFormula'] = $request->input('symbol', $request->input('chemicalFormula'));
        }
        if ($request->has('code')) $data['code'] = $request->input('code');
        if ($request->has('hsCode') || $request->has('hs_code')) {
            $data['hsCode'] = $request->input('hsCode', $request->input('hs_code'));
        }
        if ($request->has('basel_code') || $request->has('base_code_annexix')) {
            $data['basel_code'] = $request->input('basel_code', $request->input('base_code_annexix'));
        }
        if ($request->has('unitOfMeasurement') || $request->has('uom')) {
            $data['unitOfMeasurement'] = $request->input('uom', $request->input('unitOfMeasurement'));
        }
        if ($request->has('ec_waste_code') || $request->has('eu_list_of_wastes')) {
            $data['ec_waste_code'] = $request->input('ec_waste_code', $request->input('eu_list_of_wastes'));
        }
        if ($request->has('description') || $request->has('itemSpecification') || $request->has('item_specification')) {
            $data['description'] = $request->input('description') ?? $request->input('itemSpecification') ?? $request->input('item_specification');
        }
        if ($request->has('active')) {
            $data['active'] = $request->boolean('active');
        }

        $product->update($data);

        return redirect()->route('products.show', $product->id)->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['active' => false]);
        return redirect()->route('products.index')->with('success', 'Product removed successfully');
    }
}
