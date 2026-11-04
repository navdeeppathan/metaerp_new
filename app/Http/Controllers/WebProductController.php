<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WebProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('active', true);

        if ($request->filled('category')) {
            $query->where('productCategory', $request->input('category'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('chemicalFormula', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20);
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
            'category' => 'nullable|string',
            'chemicalFormula' => 'nullable|string',
            'symbol' => 'nullable|string',
            'code' => 'nullable|string|max:64',
            'hsCode' => 'nullable|string',
            'basel_code' => 'nullable|string',
            'unitOfMeasurement' => 'nullable|string',
            'uom' => 'nullable|string',
            'ec_waste_code' => 'nullable|string',
            'description' => 'nullable|string',
            'itemSpecification' => 'nullable|string',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'productCategory' => $validated['productCategory'] ?? $validated['category'] ?? 'COMMODITIES',
            'chemicalFormula' => $validated['chemicalFormula'] ?? $validated['symbol'] ?? null,
            'code' => $validated['code'] ?? null,
            'hsCode' => $validated['hsCode'] ?? null,
            'basel_code' => $validated['basel_code'] ?? null,
            'unitOfMeasurement' => $validated['unitOfMeasurement'] ?? $validated['uom'] ?? 'MT',
            'ec_waste_code' => $validated['ec_waste_code'] ?? null,
            'description' => $validated['description'] ?? $validated['itemSpecification'] ?? null,
            'active' => true,
        ]);

        return redirect()->route('products.show', $product->id)->with('success', 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $data = [];
        if ($request->has('name')) $data['name'] = $request->input('name');
        if ($request->has('productCategory') || $request->has('category')) {
            $data['productCategory'] = $request->input('productCategory', $request->input('category'));
        }
        if ($request->has('chemicalFormula') || $request->has('symbol')) {
            $data['chemicalFormula'] = $request->input('chemicalFormula', $request->input('symbol'));
        }
        if ($request->has('code')) $data['code'] = $request->input('code');
        if ($request->has('hsCode')) $data['hsCode'] = $request->input('hsCode');
        if ($request->has('basel_code')) $data['basel_code'] = $request->input('basel_code');
        if ($request->has('unitOfMeasurement') || $request->has('uom')) {
            $data['unitOfMeasurement'] = $request->input('unitOfMeasurement', $request->input('uom'));
        }
        if ($request->has('ec_waste_code')) $data['ec_waste_code'] = $request->input('ec_waste_code');
        if ($request->has('description') || $request->has('itemSpecification')) {
            $data['description'] = $request->input('description', $request->input('itemSpecification'));
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
