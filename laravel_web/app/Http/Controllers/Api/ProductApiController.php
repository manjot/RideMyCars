<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category' => function ($q) {
            $q->select('id', 'name', 'slug');
        }]);

        if ($request->has('category_id') && !empty($request->input('category_id'))) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('unit') && !empty($request->input('unit'))) {
            $query->where('unit', strtolower(trim($request->input('unit'))));
        }

        if (!$request->boolean('include_inactive')) {
            $query->where('status', 'active');
        }

        $products = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $unitKeys = implode(',', array_keys(Product::$unitOptions));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|in:' . $unitKeys,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ], [
            'unit.required' => 'Please select a unit.',
            'unit.in' => 'The selected unit is invalid.',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        $product = Product::create($validated);
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Display specified product.
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Update specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found'], 404);
        }

        $unitKeys = implode(',', array_keys(Product::$unitOptions));

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'unit' => 'sometimes|required|string|in:' . $unitKeys,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'status' => 'sometimes|required|string|in:active,inactive',
        ], [
            'unit.required' => 'Please select a unit.',
            'unit.in' => 'The selected unit is invalid.',
        ]);

        $product->update($validated);
        $product->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Remove specified product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'error' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
