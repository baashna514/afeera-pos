@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">New Sale Invoice</h2>
            <p class="text-xs text-slate-500 mt-0.5">Create sale invoice, adjust customer khata/ledger, reduce stock in base units, and print thermal receipt.</p>
        </div>
        <a href="{{ route('sales.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="saleForm" class="space-y-6">
        @csrf
        <input type="hidden" name="sale_order_id" id="sale_order_id" value="{{ $selectedSo ? $selectedSo->id : old('sale_order_id') }}">

        @if(company_has_feature('sale_orders'))
        <!-- SO Converter Card (Auto-fetch Dropdown) -->
        <div class="bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-blue-500/5 rounded-2xl border border-blue-200/80 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Convert from Sale Order (Booking)</h3>
                        <p class="text-xs text-slate-500">Auto-fill customer, ordered items, packaging units &amp; agreed prices.</p>
                    </div>
                </div>
                <div class="sm:w-80">
                    <select id="so_selector" onchange="onSoSelect(this.value)" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-xs">
                        <option value="">-- Choose Pending Sale Order (Optional) --</option>
                        @foreach ($pendingOrders as $so)
                            <option value="{{ $so->id }}" {{ ($selectedSo && $selectedSo->id == $so->id) || old('sale_order_id') == $so->id ? 'selected' : '' }}>
                                {{ $so->so_number }} - {{ $so->customer->name ?? 'Walk-in Customer' }} (Rs. {{ number_format($so->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user-tag text-blue-600"></i> Customer &amp; Invoice Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Customer Select -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="customer_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Customer <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openQuickCustomerModal()" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> Add New Customer
                        </button>
                    </div>
                    <select name="customer_id" id="customer_id" required 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('customer_id') border-rose-400 @enderror">
                        <option value="">Select Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (old('customer_id', $selectedSo?->customer_id ?? company_setting('default_customer_id')) == $customer->id) ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->phone ?? 'No phone' }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warehouse / Branch Selection -->
                <div>
                    <label for="warehouse_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        <i class="fa-solid fa-warehouse text-blue-600 mr-1"></i> Warehouse Location <span class="text-rose-500">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouse_id" onchange="onWarehouseChange()" required
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition font-semibold @error('warehouse_id') border-rose-400 @enderror">
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (old('warehouse_id') == $wh->id || ($loop->first && !old('warehouse_id'))) ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code ?? 'WH-'.$wh->id }}) {{ $wh->is_default ? '[Default]' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Invoice Date -->
                <div>
                    <label for="sale_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Invoice Date</label>
                    <input type="date" name="sale_date" id="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Extra Field One -->
                <div>
                    <label for="extra_field_one" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Reference / Extra Field</label>
                    <input type="text" name="extra_field_one" id="extra_field_one" value="{{ old('extra_field_one') }}" placeholder="e.g. PO Ref, Delivery Note..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Description / Remarks -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description / Remarks</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $selectedSo ? 'Converted from Sale Order: ' . $selectedSo->so_number : '') }}" placeholder="Special instructions, warranty terms..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Internal Note -->
                <div class="md:col-span-3">
                    <label for="note" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Internal Note (Optional)</label>
                    <input type="text" name="note" id="note" value="{{ old('note') }}" placeholder="Internal staff note..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Sale Items Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-blue-600"></i> Sold Products
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select products, packaging units, row discounts, and sale price.</p>
                </div>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg border border-blue-200 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-3" style="width: 25%;">Product</th>
                            <th class="px-3 py-3" style="width: 15%;">Unit / Packaging</th>
                            <th class="px-3 py-3 text-center" style="width: 12%;">Quantity</th>
                            <th class="px-3 py-3 text-right" style="width: 13%;">Price (Rs.)</th>
                            <th class="px-3 py-3 text-center" style="width: 12%;">Perc disc (%)</th>
                            <th class="px-3 py-3 text-center" style="width: 13%;">Amount disc (Rs.)</th>
                            <th class="px-3 py-3 text-right" style="width: 10%;">Subtotal</th>
                            <th class="px-2 py-3 text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer" class="divide-y divide-slate-100">
                        <!-- Rows injected via JS -->
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-slate-600 text-xs uppercase tracking-wider">Items Subtotal:</td>
                            <td class="px-3 py-3 text-right text-slate-800 text-sm font-black" id="itemsSubtotalDisplay">Rs. 0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('items')
                <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Invoice Discount & Payment Settlement Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <!-- Overall Invoice Discount Section -->
            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="has_overall_discount" id="has_overall_discount" value="1" onchange="toggleOverallDiscount()" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">is discount? (Overall Invoice Discount)</span>
                    </label>
                    <span class="text-xs font-semibold text-slate-500">Apply invoice-level discount</span>
                </div>

                <div id="overallDiscountControls" class="hidden grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-200/70 items-center">
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Discount Method</span>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="radio" name="overall_discount_type" value="percentage" checked onchange="updateGrandTotal()" class="text-blue-600 focus:ring-blue-500">
                                <span>Percentage (%)</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="radio" name="overall_discount_type" value="fixed" onchange="updateGrandTotal()" class="text-blue-600 focus:ring-blue-500">
                                <span>Fixed Amount (Rs.)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="overall_discount_value" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Discount Value</label>
                        <input type="number" step="0.01" min="0" name="overall_discount_value" id="overall_discount_value" value="{{ old('overall_discount_value', 0) }}" oninput="updateGrandTotal()"
                               placeholder="e.g. 10 or 500" class="w-full px-3 py-2 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="text-right">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Calculated Discount Amount</span>
                        <span class="text-sm font-black text-rose-600" id="overallDiscountAmountDisplay">- Rs. 0.00</span>
                    </div>
                </div>
            </div>

            <!-- Grand Total Summary Banner -->
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 bg-slate-900 text-white rounded-xl shadow-inner gap-4">
                <div class="flex items-center gap-4">
                    <div class="text-slate-400 text-xs uppercase tracking-wider font-bold">
                        Net Payable Grand Total
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-400" id="grandTotalDisplay">
                    Rs. 0.00
                </div>
            </div>

            <!-- Payment Settlement Controls -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="w-full px-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none font-semibold">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>

                <!-- Paid Amount -->
                <div>
                    <label for="paid_amount" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Paid Amount (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" oninput="calculatePaymentBalance()"
                           class="w-full px-4 py-2.5 text-sm font-bold bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Leave 0 for Unpaid / Credit</p>
                </div>

                <!-- Live Status & Balance Summary -->
                <div class="flex flex-col justify-center space-y-1">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 font-semibold">Remaining Due (Khata):</span>
                        <span id="dueAmountDisplay" class="font-bold text-rose-600">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 font-semibold">Invoice Status:</span>
                        <span id="statusBadgeDisplay" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800">Unpaid</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between p-6 bg-slate-900 text-white rounded-2xl shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Complete Action</p>
                    <p class="text-sm font-bold text-slate-100">Stock will be reduced in Base Units &amp; Receipt printed immediately.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('sales.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Complete Sale &amp; Print Receipt</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Product & SO list JSON for JavaScript -->
<script>
    const availableProducts = @json($products);
    const pendingOrdersList = @json($pendingOrders);
    const preselectedSo = @json($selectedSo);
    let rowIndex = 0;
    let netGrandTotal = 0;

    function toggleOverallDiscount() {
        const check = document.getElementById('has_overall_discount');
        const controls = document.getElementById('overallDiscountControls');
        if (check && check.checked) {
            controls.classList.remove('hidden');
            controls.classList.add('grid');
        } else {
            controls.classList.add('hidden');
            controls.classList.remove('grid');
        }
        updateGrandTotal();
    }

    function getProductUnits(productId) {
        const product = availableProducts.find(p => p.id == productId);
        if (!product) return [];

        const list = [];
        if (product.unit) {
            list.push({
                unit_id: product.unit.id,
                name: product.unit.name,
                short_code: product.unit.short_code,
                conversion_rate: 1.0,
                selling_price: parseFloat(product.selling_price) || 0,
                is_base: true,
            });
        } else {
            list.push({
                unit_id: '',
                name: 'Base Unit',
                short_code: 'pc',
                conversion_rate: 1.0,
                selling_price: parseFloat(product.selling_price) || 0,
                is_base: true,
            });
        }

        if (product.secondary_units && product.secondary_units.length > 0) {
            product.secondary_units.forEach(su => {
                if (su.unit) {
                    let conv = parseFloat(su.conversion_rate) || 1.0;
                    if (su.operator === 'divide') {
                        conv = 1.0 / conv;
                    }
                    const price = su.sale_price !== null ? parseFloat(su.sale_price) : (parseFloat(product.selling_price) * conv);
                    list.push({
                        unit_id: su.unit.id,
                        name: su.unit.name,
                        short_code: su.unit.short_code,
                        conversion_rate: conv,
                        selling_price: price,
                        is_base: false,
                    });
                }
            });
        }

        return list;
    }

    function getWhStockForProduct(productId, warehouseId) {
        const product = availableProducts.find(p => p.id == productId);
        if (!product) return 0;
        if (!warehouseId) return parseInt(product.quantity) || 0;

        const ws = (product.warehouse_stocks || []).find(w => w.warehouse_id == warehouseId);
        return ws ? parseInt(ws.quantity) || 0 : 0;
    }

    function getProductOptionsHtml(selectedProductId = null, warehouseId = null) {
        if (!warehouseId) {
            const whSelect = document.getElementById('warehouse_id');
            warehouseId = whSelect ? whSelect.value : null;
        }

        let productOptions = '<option value="">Select a Product</option>';
        availableProducts.forEach(p => {
            const selected = (selectedProductId && selectedProductId == p.id) ? 'selected' : '';
            const baseUnit = p.unit ? p.unit.short_code : 'pcs';
            const whStock = getWhStockForProduct(p.id, warehouseId);
            const stockLabel = whStock > 0 ? `Stock: ${whStock} ${baseUnit}` : `OUT OF STOCK (0 ${baseUnit})`;
            productOptions += `<option value="${p.id}" data-wh-stock="${whStock}" ${selected}>${p.name} (${stockLabel})</option>`;
        });
        return productOptions;
    }

    function onWarehouseChange() {
        const whSelect = document.getElementById('warehouse_id');
        const warehouseId = whSelect ? whSelect.value : null;
        const whName = whSelect && whSelect.selectedIndex >= 0 ? whSelect.options[whSelect.selectedIndex].text : 'Selected Warehouse';

        document.querySelectorAll('#itemsContainer tr').forEach(row => {
            const rowId = row.id.replace('row_', '');
            const productSelect = row.querySelector('.product-select');
            const currentSelectedProductId = productSelect ? productSelect.value : '';

            if (productSelect) {
                productSelect.innerHTML = getProductOptionsHtml(currentSelectedProductId, warehouseId);
                if (currentSelectedProductId) {
                    const whStock = getWhStockForProduct(currentSelectedProductId, warehouseId);
                    if (whStock <= 0) {
                        const product = availableProducts.find(p => p.id == currentSelectedProductId);
                        alert(`Warning: Product '${product ? product.name : 'Item'}' is out of stock in ${whName}.\nProduct selection will be reset.`);
                        productSelect.value = '';
                        row.querySelector('.unit-select').innerHTML = '<option value="">Base Unit</option>';
                    }
                }
            }
            calculateSubtotal(rowId);
        });
    }

    function addItemRow(data = null) {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;
        tr.className = 'hover:bg-slate-50/50 transition';

        const whSelect = document.getElementById('warehouse_id');
        const warehouseId = whSelect ? whSelect.value : null;
        const productOptions = getProductOptionsHtml(data ? data.product_id : null, warehouseId);

        const discPercVal = data && data.discount_percentage !== undefined ? data.discount_percentage : '0.00';
        const discAmtVal = data && data.discount_amount !== undefined ? data.discount_amount : '0.00';

        tr.innerHTML = `
            <td class="p-3">
                <select name="items[${rowIndex}][product_id]" required onchange="onProductSelect(this, ${rowIndex})"
                        class="product-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition font-medium">
                    ${productOptions}
                </select>
            </td>
            <td class="p-3">
                <select name="items[${rowIndex}][unit_id]" onchange="onUnitSelect(this, ${rowIndex})"
                        class="unit-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <option value="">Base Unit</option>
                </select>
                <input type="hidden" name="items[${rowIndex}][conversion_rate]" class="conversion-rate-input" value="1">
            </td>
            <td class="p-3">
                <input type="number" min="1" value="${data ? data.quantity : 1}" name="items[${rowIndex}][quantity]" required oninput="calculateSubtotal(${rowIndex})"
                       class="qty-input w-full px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                <p class="text-[10px] font-semibold mt-0.5 text-center unit-hint-${rowIndex}"></p>
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" value="${data ? parseFloat(data.unit_price).toFixed(2) : '0.00'}" name="items[${rowIndex}][unit_price]" required oninput="calculateSubtotal(${rowIndex})"
                       class="price-input w-full px-3 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" max="100" value="${discPercVal}" name="items[${rowIndex}][discount_percentage]" oninput="onDiscPercChange(${rowIndex})"
                       class="disc-perc-input w-full px-2 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="0%">
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" value="${discAmtVal}" name="items[${rowIndex}][discount_amount]" oninput="onDiscAmountChange(${rowIndex})"
                       class="disc-amount-input w-full px-2 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:bg-white transition" placeholder="0.00">
            </td>
            <td class="p-3 text-right font-black text-slate-800 text-xs" id="subtotal_${rowIndex}">
                Rs. 0.00
            </td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeRow(${rowIndex})" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Remove Row">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);

        if (data && data.product_id) {
            updateUnitDropdown(rowIndex, data.product_id, data.unit_id);
        }

        const currIndex = rowIndex;
        rowIndex++;
        calculateSubtotal(currIndex);
    }

    function updateUnitDropdown(id, productId, selectedUnitId = null, autoPrice = true) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const product = availableProducts.find(p => p.id == productId);
        const targetUnitId = selectedUnitId || (product ? product.default_sale_unit_id : null);

        const unitSelect = row.querySelector('.unit-select');
        const units = getProductUnits(productId);

        let html = '';
        units.forEach(u => {
            const isSelected = targetUnitId ? (targetUnitId == u.unit_id) : u.is_base;
            const label = u.is_base ? `${u.name} (${u.short_code}) [Base]` : `${u.name} (= ${u.conversion_rate} Base)`;
            html += `<option value="${u.unit_id}" data-rate="${u.conversion_rate}" data-price="${u.selling_price}" data-code="${u.short_code}" ${isSelected ? 'selected' : ''}>${label}</option>`;
        });

        unitSelect.innerHTML = html;
        onUnitSelect(unitSelect, id, autoPrice);
    }

    function applyDefaultDiscount(id, product) {
        const row = document.getElementById(`row_${id}`);
        if (!row || !product) return;

        const discType = product.default_discount_type || 'percentage';
        const discVal = parseFloat(product.default_discount_value) || 0;

        const discPercInput = row.querySelector('.disc-perc-input');
        const discAmtInput = row.querySelector('.disc-amount-input');
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 1;
        const unitPrice = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const lineGross = qty * unitPrice;

        if (discType === 'percentage') {
            discPercInput.value = discVal > 0 ? discVal.toFixed(2) : '0.00';
            const calculatedAmt = (lineGross * discVal) / 100;
            discAmtInput.value = calculatedAmt > 0 ? calculatedAmt.toFixed(2) : '0.00';
        } else {
            discAmtInput.value = discVal > 0 ? discVal.toFixed(2) : '0.00';
            const calculatedPerc = lineGross > 0 ? (discVal / lineGross) * 100 : 0;
            discPercInput.value = calculatedPerc > 0 ? calculatedPerc.toFixed(2) : '0.00';
        }
    }

    function onProductSelect(selectElement, id) {
        const productId = selectElement.value;
        const whSelect = document.getElementById('warehouse_id');
        const warehouseId = whSelect ? whSelect.value : null;
        const whName = whSelect && whSelect.selectedIndex >= 0 ? whSelect.options[whSelect.selectedIndex].text : 'Selected Warehouse';

        if (!productId) {
            document.getElementById(`row_${id}`).querySelector('.unit-select').innerHTML = '<option value="">Base Unit</option>';
            calculateSubtotal(id);
            return;
        }

        const whStock = getWhStockForProduct(productId, warehouseId);
        const product = availableProducts.find(p => p.id == productId);

        if (whStock <= 0) {
            alert(`Stock Error: '${product ? product.name : 'Product'}' has NO available stock in ${whName}.\n(Available Stock: 0)\n\nPlease select a different product or switch warehouse location.`);
            selectElement.value = '';
            document.getElementById(`row_${id}`).querySelector('.unit-select').innerHTML = '<option value="">Base Unit</option>';
            calculateSubtotal(id);
            return;
        }

        updateUnitDropdown(id, productId, null, true);
        applyDefaultDiscount(id, product);
        calculateSubtotal(id);
    }

    function onUnitSelect(unitSelect, id, autoPrice = true) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const product = availableProducts.find(p => p.id == row.querySelector('.product-select')?.value);
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rate = parseFloat(selectedOption?.getAttribute('data-rate') || 1.0);
        let unitPrice = parseFloat(selectedOption?.getAttribute('data-price'));
        if (isNaN(unitPrice) || unitPrice <= 0) {
            unitPrice = (parseFloat(product?.selling_price) || 0) * rate;
        }

        row.querySelector('.conversion-rate-input').value = rate;

        if (autoPrice) {
            row.querySelector('.price-input').value = unitPrice.toFixed(2);
        }

        calculateSubtotal(id);
    }

    function onDiscPercChange(id) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const discPerc = parseFloat(row.querySelector('.disc-perc-input')?.value) || 0;
        const lineGross = qty * price;

        const discAmt = (lineGross * discPerc) / 100;
        row.querySelector('.disc-amount-input').value = discAmt > 0 ? discAmt.toFixed(2) : '0.00';

        calculateSubtotal(id);
    }

    function onDiscAmountChange(id) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const discAmt = parseFloat(row.querySelector('.disc-amount-input')?.value) || 0;
        const lineGross = qty * price;

        const discPerc = lineGross > 0 ? (discAmt / lineGross) * 100 : 0;
        row.querySelector('.disc-perc-input').value = discPerc > 0 ? discPerc.toFixed(2) : '0.00';

        calculateSubtotal(id);
    }

    function calculateSubtotal(id) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const rateInput = row.querySelector('.conversion-rate-input');
        const discAmtInput = row.querySelector('.disc-amount-input');
        const subtotalCell = document.getElementById(`subtotal_${id}`);
        const hintEl = row.querySelector(`.unit-hint-${id}`);
        const productId = row.querySelector('.product-select')?.value;
        const whSelect = document.getElementById('warehouse_id');
        const warehouseId = whSelect ? whSelect.value : null;

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const rate = parseFloat(rateInput?.value) || 1.0;
        const discAmt = parseFloat(discAmtInput?.value) || 0;

        const gross = qty * price;
        const subtotal = Math.max(0, gross - discAmt);

        if (subtotalCell) {
            subtotalCell.innerText = 'Rs. ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        if (hintEl && productId) {
            const baseQty = qty * rate;
            const whStock = getWhStockForProduct(productId, warehouseId);
            if (baseQty > whStock) {
                hintEl.innerHTML = `<span class="text-rose-600 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> Exceeds stock (avail: ${whStock})</span>`;
            } else if (rate > 1) {
                hintEl.innerHTML = `<span class="text-blue-600">≈ ${baseQty.toLocaleString()} base (avail: ${whStock})</span>`;
            } else {
                hintEl.innerHTML = `<span class="text-slate-400">Avail in WH: ${whStock}</span>`;
            }
        } else if (hintEl) {
            hintEl.innerText = '';
        }

        updateGrandTotal();
    }

    function removeRow(id) {
        const row = document.getElementById(`row_${id}`);
        if (row) row.remove();
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let itemsTotal = 0;
        const container = document.getElementById('itemsContainer');
        const rows = container.querySelectorAll('tr');

        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(r.querySelector('.price-input')?.value) || 0;
            const discAmt = parseFloat(r.querySelector('.disc-amount-input')?.value) || 0;
            itemsTotal += Math.max(0, (qty * price) - discAmt);
        });

        document.getElementById('itemsSubtotalDisplay').innerText = 'Rs. ' + itemsTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Calculate overall invoice discount
        let overallDiscAmt = 0;
        const hasDiscCheck = document.getElementById('has_overall_discount');
        if (hasDiscCheck && hasDiscCheck.checked) {
            const discTypeRadio = document.querySelector('input[name="overall_discount_type"]:checked');
            const discType = discTypeRadio ? discTypeRadio.value : 'percentage';
            const discVal = parseFloat(document.getElementById('overall_discount_value')?.value) || 0;

            if (discType === 'percentage') {
                overallDiscAmt = (itemsTotal * discVal) / 100;
            } else {
                overallDiscAmt = Math.min(itemsTotal, discVal);
            }
        }

        const overallDisplay = document.getElementById('overallDiscountAmountDisplay');
        if (overallDisplay) {
            overallDisplay.innerText = '- Rs. ' + overallDiscAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        netGrandTotal = Math.max(0, itemsTotal - overallDiscAmt);
        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + netGrandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Auto default paid amount to net grand total if currently equal or 0
        const paidInput = document.getElementById('paid_amount');
        if (paidInput && (parseFloat(paidInput.value) === 0 || paidInput.dataset.autoFilled === 'true')) {
            paidInput.value = netGrandTotal.toFixed(2);
            paidInput.dataset.autoFilled = 'true';
        }

        calculatePaymentBalance();
    }

    function calculatePaymentBalance() {
        const paid = parseFloat(document.getElementById('paid_amount')?.value) || 0;
        const due = Math.max(0, netGrandTotal - paid);
        
        document.getElementById('dueAmountDisplay').innerText = 'Rs. ' + due.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const badge = document.getElementById('statusBadgeDisplay');
        if (paid >= netGrandTotal && netGrandTotal > 0) {
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800';
            badge.innerText = 'Paid';
        } else if (paid > 0) {
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800';
            badge.innerText = 'Partially Paid';
        } else {
            badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800';
            badge.innerText = 'Unpaid';
        }
    }

    function onSoSelect(soId) {
        if (!soId) {
            document.getElementById('sale_order_id').value = '';
            return;
        }

        const so = pendingOrdersList.find(p => p.id == soId);
        if (!so) return;

        document.getElementById('sale_order_id').value = so.id;
        if (so.customer_id) {
            document.getElementById('customer_id').value = so.customer_id;
        }
        document.getElementById('description').value = `Converted from Sale Order: ${so.so_number}`;

        document.getElementById('itemsContainer').innerHTML = '';
        rowIndex = 0;

        if (so.items && so.items.length > 0) {
            so.items.forEach(item => {
                addItemRow({
                    product_id: item.product_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (preselectedSo && preselectedSo.items && preselectedSo.items.length > 0) {
            onSoSelect(preselectedSo.id);
        } else {
            addItemRow();
        }
    });
</script>

<!-- Quick Add Customer Modal -->
<div id="quickCustomerModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-800">Add New Customer</h3>
                <p class="text-xs text-slate-500">Add customer on the fly without page reload</p>
            </div>
            <button type="button" onclick="closeQuickCustomerModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="quickCustomerForm" onsubmit="submitQuickCustomer(event)" class="space-y-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Customer Name *</label>
                <input type="text" id="qc_name" required placeholder="e.g. Ali Khan"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Phone Number</label>
                <input type="text" id="qc_phone" placeholder="e.g. 0300-1234567"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                <input type="email" id="qc_email" placeholder="customer@example.com"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Address / City</label>
                <input type="text" id="qc_address" placeholder="Lahore, Karachi, etc."
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeQuickCustomerModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" id="qc_btn" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">Save &amp; Select Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickCustomerModal() {
        const modal = document.getElementById('quickCustomerModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('qc_name').focus();
    }

    function closeQuickCustomerModal() {
        const modal = document.getElementById('quickCustomerModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function submitQuickCustomer(e) {
        e.preventDefault();
        const btn = document.getElementById('qc_btn');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        const payload = {
            name: document.getElementById('qc_name').value,
            phone: document.getElementById('qc_phone').value,
            email: document.getElementById('qc_email').value,
            address: document.getElementById('qc_address').value,
        };

        try {
            const res = await fetch("{{ route('customers.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (data.success && data.customer) {
                const customerSelect = document.getElementById('customer_id');
                const opt = new Option(`${data.customer.name} (${data.customer.phone || 'No phone'})`, data.customer.id, true, true);
                customerSelect.add(opt);
                closeQuickCustomerModal();
                document.getElementById('quickCustomerForm').reset();
            } else {
                alert(data.message || 'Error saving customer.');
            }
        } catch (err) {
            console.error(err);
            alert('Failed to save customer. Please check all fields.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Save & Select Customer';
        }
    }
</script>
@endsection