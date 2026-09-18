@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Edit Product</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update details, barcode, pricing or stock threshold.</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Product Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required 
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('name') border-rose-400 bg-rose-50/20 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Barcode -->
                <div>
                    <label for="barcode" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Barcode / SKU <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}" required 
                               class="w-full pl-10 pr-4 py-2.5 font-mono text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('barcode') border-rose-400 bg-rose-50/20 @enderror">
                    </div>
                    @error('barcode')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                @if(company_has_feature('categories'))
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="category_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Category <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openQuickCategoryModal()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> Add New Category
                        </button>
                    </div>
                    <select name="category_id" id="category_id" required 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('category_id') border-rose-400 @enderror">
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Base Unit -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="unit_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Base Unit</label>
                        <button type="button" onclick="openQuickUnitModal()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> Add New Unit
                        </button>
                    </div>
                    <select name="unit_id" id="unit_id"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('unit_id') border-rose-400 @enderror">
                        <option value="">Select Unit (Piece, Box, Kg...)</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id', $product->unit_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->short_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Brand -->
                @if(company_has_feature('brands'))
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="brand_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Brand</label>
                        <button type="button" onclick="openQuickBrandModal()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> Add New Brand
                        </button>
                    </div>
                    <select name="brand_id" id="brand_id" 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('brand_id') border-rose-400 @enderror">
                        <option value="">Select Brand (Optional)</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Purchase Price -->
                <div>
                    <label for="purchase_price" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Purchase / Cost Price (Rs.) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rs.</span>
                        <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required
                               class="w-full pl-12 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('purchase_price') border-rose-400 @enderror">
                    </div>
                    @error('purchase_price')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Selling Price -->
                <div>
                    <label for="selling_price" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Selling / Retail Price (Rs.) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rs.</span>
                        <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required
                               class="w-full pl-12 pr-4 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('selling_price') border-rose-400 @enderror">
                    </div>
                    @error('selling_price')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Default Discount Type -->
                <div>
                    <label for="default_discount_type" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Default Sale Discount Type</label>
                    <select name="default_discount_type" id="default_discount_type" 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                        <option value="percentage" {{ old('default_discount_type', $product->default_discount_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('default_discount_type', $product->default_discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs.)</option>
                    </select>
                </div>

                <!-- Default Discount Value -->
                <div>
                    <label for="default_discount_value" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Default Sale Discount Value</label>
                    <input type="number" step="0.01" min="0" name="default_discount_value" id="default_discount_value" value="{{ old('default_discount_value', $product->default_discount_value ?? '0.00') }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">
                    <span class="text-[10px] text-slate-400">Auto-filled in sales invoice when selecting this product.</span>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Current Quantity in Stock <span class="text-rose-500">*</span></label>
                    <input type="number" min="0" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('quantity') border-rose-400 @enderror">
                    @error('quantity')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alert Quantity -->
                <div>
                    <label for="alert_quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Low Stock Alert Threshold <span class="text-rose-500">*</span></label>
                    <input type="number" min="0" name="alert_quantity" id="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition @error('alert_quantity') border-rose-400 @enderror">
                    @error('alert_quantity')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description / Specifications (Optional)</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <!-- Warehouse Stock Allocation (Multi-Warehouse Initial Stock) -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-warehouse text-brand-600"></i> Warehouses &amp; Stock Allocation (Optional)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Assign stock directly across multiple warehouses. Total stock will calculate automatically.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openQuickWarehouseModal()" class="px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold rounded-lg border border-brand-200 flex items-center gap-1 transition">
                            <i class="fa-solid fa-plus-circle"></i> New Warehouse
                        </button>
                        <button type="button" onclick="addWarehouseStockRow()" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus"></i> Add Row
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl bg-slate-50/50">
                    <table class="w-full text-left text-xs" id="warehouseStocksTable">
                        <thead class="bg-slate-100 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5" style="width: 55%;">Warehouse Location</th>
                                <th class="px-3 py-2.5" style="width: 40%;">Allocated Quantity</th>
                                <th class="px-2 py-2.5 text-center" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="warehouseStocksContainer" class="divide-y divide-slate-200/70">
                            <!-- Dynamic rows injected via JS -->
                        </tbody>
                    </table>
                    <div id="noWarehouseStocksMsg" class="p-4 text-center text-xs text-slate-400">
                        No warehouse stock rows added yet. If left empty, stock is assigned to your <strong>Default Warehouse</strong>. Click <strong>"+ Add Warehouse Stock"</strong> to split stock across locations.
                    </div>
                </div>
            </div>

            <!-- Secondary Units Configuration Card -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-brand-600"></i> Secondary Units &amp; Packaging (Optional)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Define alternative packaging (e.g., 1 Box = 12 Pieces, 1 Carton = 24 Pieces) with custom prices.</p>
                    </div>
                    <button type="button" onclick="addSecondaryUnitRow()" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold rounded-lg border border-brand-200 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-plus"></i> Add Secondary Unit
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl bg-slate-50/50">
                    <table class="w-full text-left text-xs" id="secondaryUnitsTable">
                        <thead class="bg-slate-100 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5" style="width: 35%;">Packaging Unit</th>
                                <th class="px-3 py-2.5" style="width: 30%;">Operator & Rate</th>
                                <th class="px-3 py-2.5" style="width: 30%;">Calculated Prices (Base ×/÷ Rate)</th>
                                <th class="px-2 py-2.5 text-center" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="secondaryUnitsContainer" class="divide-y divide-slate-200/70">
                            <!-- Dynamic rows injected via JS -->
                        </tbody>
                    </table>
                    <div id="noSecondaryUnitsMsg" class="p-4 text-center text-xs text-slate-400">
                        No secondary units configured yet. Click <strong>"+ Add Secondary Unit"</strong> to add box, carton, or pack conversions.
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-brand-600"></i>
                    <span><strong>Auto Pricing:</strong> Prices for packaging units are always dynamically computed as <code>Base Price × Conversion Rate</code>. If base price changes, secondary unit prices update automatically.</span>
                </p>

                <!-- Default Units Preference -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-brand-50/60 rounded-xl border border-brand-200/80 mt-4">
                    <div>
                        <label for="default_sale_unit_id" class="block text-xs font-bold uppercase tracking-wider text-brand-800 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-cart-shopping text-brand-600"></i> Default Sale Unit
                        </label>
                        <select name="default_sale_unit_id" id="default_sale_unit_id" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-brand-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="">Default: Use Base Unit</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}" {{ old('default_sale_unit_id', $product->default_sale_unit_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->short_code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Automatically chosen in POS &amp; Sale Invoices so you don't have to select it every time.</p>
                    </div>

                    <div>
                        <label for="default_purchase_unit_id" class="block text-xs font-bold uppercase tracking-wider text-brand-800 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-truck-ramp-box text-brand-600"></i> Default Purchase Unit
                        </label>
                        <select name="default_purchase_unit_id" id="default_purchase_unit_id" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-brand-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="">Default: Use Base Unit</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}" {{ old('default_purchase_unit_id', $product->default_purchase_unit_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->short_code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Automatically chosen in Purchase Invoices with converted cost price.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-md shadow-brand-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Update Product</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Category Modal -->
<div id="quickCategoryModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-brand-600"></i> Quick Add Category
            </h3>
            <button type="button" onclick="closeQuickCategoryModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickCategory(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_cat_name" required placeholder="e.g. Electronics, Hardware..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description (Optional)</label>
                <input type="text" id="qc_cat_desc" placeholder="Brief category notes..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickCategoryModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qc_cat_submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Brand Modal -->
<div id="quickBrandModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-copyright text-brand-600"></i> Quick Add Brand
            </h3>
            <button type="button" onclick="closeQuickBrandModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickBrand(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Brand Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_brand_name" required placeholder="e.g. Samsung, Nike, Nestle..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Description (Optional)</label>
                <input type="text" id="qc_brand_desc" placeholder="Brief brand notes..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickBrandModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qc_brand_submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Unit Modal -->
<div id="quickUnitModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-brand-600"></i> Quick Add Unit
            </h3>
            <button type="button" onclick="closeQuickUnitModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickUnit(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Unit Full Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_unit_name" required placeholder="e.g. Kilogram, Carton, Piece..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Short Code <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_unit_code" required placeholder="e.g. kg, ctn, pc..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono font-bold">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickUnitModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qc_unit_submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Warehouse Modal -->
<div id="quickWarehouseModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-brand-600"></i> Quick Add Warehouse
            </h3>
            <button type="button" onclick="closeQuickWarehouseModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickWarehouse(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Warehouse Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_wh_name" required placeholder="e.g. Main Godown, North Branch..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Code</label>
                    <input type="text" id="qc_wh_code" placeholder="e.g. WH-02" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Phone</label>
                    <input type="text" id="qc_wh_phone" placeholder="Contact number..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Address</label>
                <input type="text" id="qc_wh_address" placeholder="Physical location..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickWarehouseModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qc_wh_submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const availableUnitsList = @json($units);
    const availableWarehousesList = @json($warehouses);
    const existingSecondaryUnits = @json($product->secondaryUnits);
    const existingWarehouseStocks = @json($product->warehouseStocks);
    let secondaryUnitIndex = 0;
    let warehouseStockIndex = 0;

    function addWarehouseStockRow(data = null) {
        const container = document.getElementById('warehouseStocksContainer');
        const emptyMsg = document.getElementById('noWarehouseStocksMsg');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const tr = document.createElement('tr');
        tr.id = `ws_row_${warehouseStockIndex}`;
        tr.className = 'hover:bg-white transition';

        let options = '<option value="">Select Warehouse</option>';
        availableWarehousesList.forEach(w => {
            const selected = data && data.warehouse_id == w.id ? 'selected' : (!data && w.is_default ? 'selected' : '');
            options += `<option value="${w.id}" ${selected}>${w.name} (${w.code || 'WH-' + w.id})</option>`;
        });

        const qty = data ? data.quantity : 0;

        tr.innerHTML = `
            <td class="p-2.5">
                <select name="warehouse_stocks[${warehouseStockIndex}][warehouse_id]" required
                        class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                    ${options}
                </select>
            </td>
            <td class="p-2.5">
                <input type="number" min="0" name="warehouse_stocks[${warehouseStockIndex}][quantity]" value="${qty}" required placeholder="0"
                       oninput="calculateTotalStockFromWarehouses()"
                       class="wh-qty-input w-full px-2.5 py-1.5 text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </td>
            <td class="p-2.5 text-center">
                <button type="button" onclick="removeWarehouseStockRow(${warehouseStockIndex})" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Remove">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);
        warehouseStockIndex++;
        calculateTotalStockFromWarehouses();
    }

    function removeWarehouseStockRow(index) {
        const row = document.getElementById(`ws_row_${index}`);
        if (row) row.remove();

        const container = document.getElementById('warehouseStocksContainer');
        if (container.querySelectorAll('tr').length === 0) {
            const emptyMsg = document.getElementById('noWarehouseStocksMsg');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
        }
        calculateTotalStockFromWarehouses();
    }

    function calculateTotalStockFromWarehouses() {
        const rows = document.querySelectorAll('#warehouseStocksContainer tr');
        if (rows.length > 0) {
            let total = 0;
            rows.forEach(r => {
                const inp = r.querySelector('.wh-qty-input');
                if (inp) total += parseInt(inp.value) || 0;
            });
            const mainQtyInput = document.getElementById('quantity');
            if (mainQtyInput) mainQtyInput.value = total;
        }
    }

    function updateAllUnitPricePreviews() {
        const baseSale = parseFloat(document.getElementById('selling_price')?.value) || 0;
        const baseBuy = parseFloat(document.getElementById('purchase_price')?.value) || 0;

        document.querySelectorAll('#secondaryUnitsContainer tr').forEach(row => {
            const rateInput = row.querySelector('.rate-input');
            const operatorSelect = row.querySelector('.operator-select');
            const previewEl = row.querySelector('.price-preview');
            if (rateInput && previewEl) {
                const rate = parseFloat(rateInput.value) || 0;
                const op = operatorSelect ? operatorSelect.value : 'multiply';
                if (rate > 0) {
                    const sPrice = op === 'multiply' ? (baseSale * rate) : (baseSale / rate);
                    const bPrice = op === 'multiply' ? (baseBuy * rate) : (baseBuy / rate);
                    previewEl.innerHTML = `<span class="text-brand-700 font-bold">Sell: Rs. ${Number(sPrice.toFixed(2)).toLocaleString()}</span> <span class="text-slate-300 mx-1">|</span> <span class="text-slate-600">Buy: Rs. ${Number(bPrice.toFixed(2)).toLocaleString()}</span>`;
                } else {
                    previewEl.innerHTML = `<span class="text-slate-400 italic">Enter rate to preview</span>`;
                }
            }
        });
    }

    function addSecondaryUnitRow(data = null) {
        const container = document.getElementById('secondaryUnitsContainer');
        const emptyMsg = document.getElementById('noSecondaryUnitsMsg');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const tr = document.createElement('tr');
        tr.id = `su_row_${secondaryUnitIndex}`;
        tr.className = 'hover:bg-white transition';

        const baseUnitId = document.getElementById('unit_id')?.value;
        let options = '<option value="">Select Unit</option>';
        availableUnitsList.forEach(u => {
            if (u.id != baseUnitId) {
                const selected = data && data.unit_id == u.id ? 'selected' : '';
                options += `<option value="${u.id}" ${selected}>${u.name} (${u.short_code})</option>`;
            }
        });

        const conversion = data ? data.conversion_rate : '';
        const operator = data && data.operator ? data.operator : 'multiply';

        tr.innerHTML = `
            <td class="p-2.5">
                <select name="secondary_units[${secondaryUnitIndex}][unit_id]" required
                        class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    ${options}
                </select>
            </td>
            <td class="p-2.5">
                <div class="flex items-center gap-1">
                    <select name="secondary_units[${secondaryUnitIndex}][operator]" onchange="updateAllUnitPricePreviews()" class="operator-select px-2 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none w-14">
                        <option value="multiply" ${operator === 'multiply' ? 'selected' : ''}>×</option>
                        <option value="divide" ${operator === 'divide' ? 'selected' : ''}>÷</option>
                    </select>
                    <input type="number" step="0.0001" min="0.0001" name="secondary_units[${secondaryUnitIndex}][conversion_rate]" value="${conversion}" required placeholder="e.g. 12"
                           oninput="updateAllUnitPricePreviews()"
                           class="rate-input w-full px-2.5 py-1.5 text-xs font-bold text-center bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </td>
            <td class="p-2.5">
                <div class="price-preview text-xs text-slate-700 bg-slate-100/80 px-2.5 py-1.5 rounded-lg border border-slate-200/80 flex items-center">
                    <span class="text-slate-400 italic">Enter rate to preview</span>
                </div>
            </td>
            <td class="p-2.5 text-center">
                <button type="button" onclick="removeSecondaryUnitRow(${secondaryUnitIndex})" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Remove">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);
        secondaryUnitIndex++;
        updateAllUnitPricePreviews();
    }

    function removeSecondaryUnitRow(index) {
        const row = document.getElementById(`su_row_${index}`);
        if (row) row.remove();

        const container = document.getElementById('secondaryUnitsContainer');
        if (container.querySelectorAll('tr').length === 0) {
            const emptyMsg = document.getElementById('noSecondaryUnitsMsg');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
        }
    }

    // Quick Creation Modal Handlers
    function openQuickCategoryModal() {
        const modal = document.getElementById('quickCategoryModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qc_cat_name').focus();
        }
    }

    function closeQuickCategoryModal() {
        const modal = document.getElementById('quickCategoryModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qc_cat_name').value = '';
            document.getElementById('qc_cat_desc').value = '';
        }
    }

    async function saveQuickCategory(e) {
        e.preventDefault();
        const name = document.getElementById('qc_cat_name').value.trim();
        const description = document.getElementById('qc_cat_desc').value.trim();
        if (!name) return;

        const btn = document.getElementById('qc_cat_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('categories.store.inline') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, description }),
            });

            const data = await res.json();
            if (data.success && data.category) {
                const select = document.getElementById('category_id');
                const opt = document.createElement('option');
                opt.value = data.category.id;
                opt.innerText = data.category.name;
                opt.selected = true;
                select.appendChild(opt);

                closeQuickCategoryModal();
            } else {
                alert(data.message || 'Could not save category.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating category.');
        } finally {
            btn.disabled = false;
        }
    }

    function openQuickBrandModal() {
        const modal = document.getElementById('quickBrandModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qc_brand_name').focus();
        }
    }

    function closeQuickBrandModal() {
        const modal = document.getElementById('quickBrandModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qc_brand_name').value = '';
            document.getElementById('qc_brand_desc').value = '';
        }
    }

    async function saveQuickBrand(e) {
        e.preventDefault();
        const name = document.getElementById('qc_brand_name').value.trim();
        const description = document.getElementById('qc_brand_desc').value.trim();
        if (!name) return;

        const btn = document.getElementById('qc_brand_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('brands.store.inline') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, description }),
            });

            const data = await res.json();
            if (data.success && data.brand) {
                const select = document.getElementById('brand_id');
                const opt = document.createElement('option');
                opt.value = data.brand.id;
                opt.innerText = data.brand.name;
                opt.selected = true;
                select.appendChild(opt);

                closeQuickBrandModal();
            } else {
                alert(data.message || 'Could not save brand.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating brand.');
        } finally {
            btn.disabled = false;
        }
    }

    function openQuickUnitModal() {
        const modal = document.getElementById('quickUnitModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qc_unit_name').focus();
        }
    }

    function closeQuickUnitModal() {
        const modal = document.getElementById('quickUnitModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qc_unit_name').value = '';
            document.getElementById('qc_unit_code').value = '';
        }
    }

    async function saveQuickUnit(e) {
        e.preventDefault();
        const name = document.getElementById('qc_unit_name').value.trim();
        const short_code = document.getElementById('qc_unit_code').value.trim();
        if (!name || !short_code) return;

        const btn = document.getElementById('qc_unit_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('units.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, short_code }),
            });

            const data = await res.json();
            if (data.success && data.unit) {
                availableUnitsList.push(data.unit);

                const baseSelect = document.getElementById('unit_id');
                const opt1 = document.createElement('option');
                opt1.value = data.unit.id;
                opt1.innerText = `${data.unit.name} (${data.unit.short_code})`;
                opt1.selected = true;
                baseSelect.appendChild(opt1);

                ['default_sale_unit_id', 'default_purchase_unit_id'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        const opt = document.createElement('option');
                        opt.value = data.unit.id;
                        opt.innerText = `${data.unit.name} (${data.unit.short_code})`;
                        el.appendChild(opt);
                    }
                });

                closeQuickUnitModal();
            } else {
                alert(data.message || 'Could not save unit.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating unit.');
        } finally {
            btn.disabled = false;
        }
    }

    function openQuickWarehouseModal() {
        const modal = document.getElementById('quickWarehouseModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qc_wh_name').focus();
        }
    }

    function closeQuickWarehouseModal() {
        const modal = document.getElementById('quickWarehouseModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qc_wh_name').value = '';
            document.getElementById('qc_wh_code').value = '';
            document.getElementById('qc_wh_phone').value = '';
            document.getElementById('qc_wh_address').value = '';
        }
    }

    async function saveQuickWarehouse(e) {
        e.preventDefault();
        const name = document.getElementById('qc_wh_name').value.trim();
        const code = document.getElementById('qc_wh_code').value.trim();
        const phone = document.getElementById('qc_wh_phone').value.trim();
        const address = document.getElementById('qc_wh_address').value.trim();
        if (!name) return;

        const btn = document.getElementById('qc_wh_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('warehouses.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, code, phone, address }),
            });

            const data = await res.json();
            if (data.success && data.warehouse) {
                availableWarehousesList.push(data.warehouse);
                addWarehouseStockRow(data.warehouse);
                closeQuickWarehouseModal();
            } else {
                alert(data.message || 'Could not save warehouse.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating warehouse.');
        } finally {
            btn.disabled = false;
        }
    }

    // Populate existing secondary units and warehouse stocks on load
    document.addEventListener('DOMContentLoaded', function() {
        if (existingWarehouseStocks && existingWarehouseStocks.length > 0) {
            existingWarehouseStocks.forEach(ws => {
                addWarehouseStockRow(ws);
            });
        }

        if (existingSecondaryUnits && existingSecondaryUnits.length > 0) {
            existingSecondaryUnits.forEach(su => {
                addSecondaryUnitRow(su);
            });
        }
    });
</script>
@endsection

