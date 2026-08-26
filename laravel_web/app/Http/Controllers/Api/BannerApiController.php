<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;

class BannerApiController extends Controller
{
    /**
     * Display a listing of banners filtered by category & status.
     */
    public function index(Request $request)
    {
        $query = Banner::with(['category' => function ($q) {
            $q->select('id', 'name', 'slug');
        }]);

        // Filter by category_id if provided
        if ($request->has('category_id') && !empty($request->input('category_id'))) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by category name or slug if provided (e.g. ?category=Ride or ?category=delivery)
        if ($request->has('category') && !empty($request->input('category'))) {
            $catSearch = strtolower(trim($request->input('category')));
            $query->whereHas('category', function ($q) use ($catSearch) {
                $q->whereRaw('LOWER(name) = ?', [$catSearch])
                  ->orWhereRaw('LOWER(slug) = ?', [$catSearch]);
            });
        }

        // Only return active banners unless include_inactive is explicitly set by admin
        if (!$request->boolean('include_inactive')) {
            $query->where('status', 'active');
        }

        $banners = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count' => $banners->count(),
            'data' => $banners,
        ]);
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|string',
            'link' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ], [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        $banner = Banner::create($validated);
        $banner->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
            'data' => $banner,
        ], 201);
    }

    /**
     * Display specified banner.
     */
    public function show($id)
    {
        $banner = Banner::with('category')->find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'error' => 'Banner not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $banner]);
    }

    /**
     * Update specified banner.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'error' => 'Banner not found'], 404);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'sometimes|required|string',
            'link' => 'nullable|string',
            'status' => 'sometimes|required|string|in:active,inactive',
        ], [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
        ]);

        $banner->update($validated);
        $banner->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => $banner,
        ]);
    }

    /**
     * Remove specified banner.
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'error' => 'Banner not found'], 404);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully (Category remains intact)',
        ]);
    }
}
