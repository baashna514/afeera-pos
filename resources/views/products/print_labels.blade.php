@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Print Barcode Labels</h2>
            <p class="text-xs text-slate-500 mt-0.5">Customize label details and layout before sending to printer.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Labels</span>
            </button>
        </div>
    </div>

    <!-- Print Configuration Control Panel -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6 no-print">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-sliders text-emerald-600"></i> Label Settings
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label for="label_count" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Number of Labels</label>
                <input type="number" id="label_count" value="12" min="1" max="500" oninput="renderLabels()" 
                       class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold">
            </div>

            <div>
                <label for="paper_layout" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Sheet Layout</label>
                <select id="paper_layout" onchange="renderLabels()" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-semibold">
                    <option value="grid-4">4 Columns (A4 Standard)</option>
                    <option value="grid-3">3 Columns (Wide Labels)</option>
                    <option value="grid-2">2 Columns (Large Sticker)</option>
                    <option value="single">Single Thermal Roll (Continuous)</option>
                </select>
            </div>

            <div class="md:col-span-2 flex flex-wrap items-center gap-6 pt-6">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="toggle_company" checked onchange="renderLabels()" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Include Company Name</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="toggle_price" checked onchange="renderLabels()" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Include Selling Price</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="toggle_code" checked onchange="renderLabels()" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Include Barcode Text</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Printable Sheet Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 no-print">Live Sheet Preview</h3>

        <div id="labelsSheet" class="grid grid-cols-4 gap-4 p-4 border border-slate-200/70 rounded-xl bg-slate-50/50 min-h-[300px]">
            <!-- Dynamic barcodes injected via JS -->
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: white !important; color: black !important; padding: 0 !important; margin: 0 !important; }
        .no-print { display: none !important; }
        #labelsSheet {
            display: grid !important;
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            gap: 8px !important;
        }
        .barcode-card {
            border: 1px dashed #cbd5e1 !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    const productData = {
        name: @json($product->name),
        barcode: @json($product->barcode),
        price: @json($product->selling_price),
        company: @json(auth()->user()?->company?->name ?? 'SmartPOS Store'),
    };

    function renderLabels() {
        const count = parseInt(document.getElementById('label_count').value) || 1;
        const layout = document.getElementById('paper_layout').value;
        const showCompany = document.getElementById('toggle_company').checked;
        const showPrice = document.getElementById('toggle_price').checked;
        const showCode = document.getElementById('toggle_code').checked;

        const sheet = document.getElementById('labelsSheet');
        sheet.className = '';

        if (layout === 'grid-4') {
            sheet.className = 'grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 border border-slate-200/70 rounded-xl bg-slate-50/50';
        } else if (layout === 'grid-3') {
            sheet.className = 'grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 border border-slate-200/70 rounded-xl bg-slate-50/50';
        } else if (layout === 'grid-2') {
            sheet.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 border border-slate-200/70 rounded-xl bg-slate-50/50';
        } else {
            sheet.className = 'flex flex-col items-center gap-4 p-4 border border-slate-200/70 rounded-xl bg-slate-50/50';
        }

        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="barcode-card bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-center text-center space-y-1 w-full max-w-[220px]">
                    ${showCompany ? `<span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 truncate w-full">${productData.company}</span>` : ''}
                    <span class="text-xs font-black text-slate-800 line-clamp-1 leading-tight">${productData.name}</span>
                    <svg id="barcode_svg_${i}" class="max-w-full h-12"></svg>
                    ${showPrice ? `<span class="text-xs font-black text-slate-900">Rs. ${parseFloat(productData.price).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>` : ''}
                </div>
            `;
        }

        sheet.innerHTML = html;

        // Render barcode SVG for each label card using JsBarcode
        for (let i = 0; i < count; i++) {
            try {
                JsBarcode(`#barcode_svg_${i}`, productData.barcode, {
                    format: "CODE128",
                    width: 1.5,
                    height: 38,
                    displayValue: showCode,
                    fontSize: 10,
                    margin: 2,
                });
            } catch (err) {
                console.error("JsBarcode error:", err);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', renderLabels);
</script>
@endsection
