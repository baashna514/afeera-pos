<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $brands = Brand::withCount('products')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('brands.index', compact('brands', 'search'));
    }

    public function create(): View
    {
        return view('brands.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $brand = Brand::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'brand' => $brand,
                'data' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ],
            ]);
        }

        return redirect()->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'brand' => $brand,
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }

    public function edit(Brand $brand): View
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $brand->update($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->count() > 0) {
            return redirect()->route('brands.index')
                ->with('error', 'Cannot delete brand associated with existing products.');
        }

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
