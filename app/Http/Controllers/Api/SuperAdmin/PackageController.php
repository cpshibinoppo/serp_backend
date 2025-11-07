<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $packages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'location_count' => 'nullable|integer|min:0',
            'user_count' => 'nullable|integer|min:0',
            'product_count' => 'nullable|integer|min:0',
            'invoice_count' => 'nullable|integer|min:0',
            'interval' => 'required|in:days,months,years',
            'trial_days' => 'nullable|integer|min:0',
            'created_by' => 'nullable|integer',
            'is_active' => 'boolean',
            'mark_package_as_popular' => 'boolean',
            'is_private' => 'boolean',
        ]);

        $package = Package::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Package created successfully',
            'data' => $package
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $package
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'location_count' => 'nullable|integer|min:0',
            'user_count' => 'nullable|integer|min:0',
            'product_count' => 'nullable|integer|min:0',
            'invoice_count' => 'nullable|integer|min:0',
            'interval' => 'sometimes|required|in:days,months,years',
            'trial_days' => 'nullable|integer|min:0',
            'businesses' => 'nullable|array',
            'created_by' => 'nullable|integer',
            'is_active' => 'boolean',
            'mark_package_as_popular' => 'boolean',
            'is_private' => 'boolean',
        ]);

        $package->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Package updated successfully',
            'data' => $package
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found'
            ], 404);
        }

        $package->delete();

        return response()->json([
            'status' => true,
            'message' => 'Package deleted successfully'
        ]);
    }
}
