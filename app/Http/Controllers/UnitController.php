<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $units = Unit::with('baseUnit')
            ->withCount('products')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('short_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create(): View
    {
        $baseUnits = Unit::whereNull('base_unit_id')->orderBy('name')->get();

        return view('units.create', compact('baseUnits'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
            'short_code' => ['required', 'string', 'max:20', 'unique:units,short_code'],
            'base_unit_id' => ['nullable', 'exists:units,id'],
            'operator' => ['nullable', 'in:*,/'],
            'conversion_factor' => ['nullable', 'numeric', 'min:0.0001'],
        ]);

        $validated['operator'] = $validated['operator'] ?? '*';
        $validated['conversion_factor'] = $validated['conversion_factor'] ?? 1.0;

        $unit = Unit::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully.',
                'unit' => $unit,
            ]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_code' => ['required', 'string', 'max:50'],
            'base_unit' => ['nullable', 'integer'],
            'base_unit_id' => ['nullable', 'integer'],
        ]);

        $unit = Unit::create([
            'name' => $validated['name'],
            'short_code' => $validated['short_code'],
            'base_unit_id' => $validated['base_unit_id'] ?? $validated['base_unit'] ?? null,
            'operator' => '*',
            'conversion_factor' => 1.0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully',
            'data' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_code' => $unit->short_code,
            ],
            'unit' => $unit,
        ]);
    }

    public function edit(Unit $unit): View
    {
        $baseUnits = Unit::whereNull('base_unit_id')
            ->where('id', '!=', $unit->id)
            ->orderBy('name')
            ->get();

        return view('units.edit', compact('unit', 'baseUnits'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name,'.$unit->id],
            'short_code' => ['required', 'string', 'max:20', 'unique:units,short_code,'.$unit->id],
            'base_unit_id' => ['nullable', 'exists:units,id'],
            'operator' => ['required', 'in:*,/'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Cannot delete unit assigned to products.');
        }

        if ($unit->subUnits()->count() > 0) {
            return back()->with('error', 'Cannot delete unit because other units depend on it as a base unit.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}
