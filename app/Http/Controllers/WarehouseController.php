<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount('stocks')
            ->withSum('stocks as total_quantity', 'quantity')
            ->latest()
            ->paginate(15);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        $companyId = Auth::user()?->company_id;

        if (! empty($validated['is_default'])) {
            Warehouse::where('company_id', $companyId)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'code' => ! empty($validated['code']) ? $validated['code'] : 'WH-'.rand(100, 999),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'is_default' => $request->has('is_default') ? (bool) $request->is_default : false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully.',
                'warehouse' => $warehouse,
            ]);
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
        ]);

        $companyId = Auth::user()?->company_id;

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'code' => ! empty($validated['code']) ? $validated['code'] : 'WH-'.rand(100, 999),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
            ],
            'warehouse' => $warehouse,
        ]);
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
        ]);

        $companyId = Auth::user()?->company_id;

        if ($request->has('is_default') && $request->is_default) {
            Warehouse::where('company_id', $companyId)
                ->where('id', '!=', $warehouse->id)
                ->update(['is_default' => false]);
        }

        $warehouse->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? $warehouse->code,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : false,
            'is_default' => $request->has('is_default') ? (bool) $request->is_default : false,
        ]);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->is_default) {
            return back()->with('error', 'Cannot delete default warehouse.');
        }

        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete warehouse containing stock items. Transfer or clear stock first.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
