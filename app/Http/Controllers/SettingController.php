<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Services\CompanySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings management view.
     */
    public function index(Request $request): View
    {
        $companyId = CompanySettingService::resolveCompanyId();
        $company = $companyId ? Company::find($companyId) : Company::first();

        $settings = CompanySettingService::all($company?->id);
        $customers = Customer::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $activeTab = $request->query('tab', 'features');

        return view('settings.index', compact(
            'company',
            'settings',
            'customers',
            'vendors',
            'warehouses',
            'activeTab'
        ));
    }

    /**
     * Update General company information.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $companyId = CompanySettingService::resolveCompanyId();
        $company = $companyId ? Company::find($companyId) : Company::first();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies', 'public');
            CompanySettingService::set('branding.logo_dark', $logoPath, $company?->id);
            CompanySettingService::set('branding.logo_light', $logoPath, $company?->id);
        }

        if ($company) {
            $updateData = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'currency' => $validated['currency'],
            ];
            if ($logoPath) {
                $updateData['logo'] = $logoPath;
            }
            $company->update($updateData);
        }

        CompanySettingService::setMany([
            'general.app_name' => $validated['name'],
            'general.phone' => $validated['phone'] ?? '',
            'general.email' => $validated['email'] ?? '',
            'general.address' => $validated['address'] ?? '',
            'general.currency' => $validated['currency'],
            'general.currency_symbol' => $validated['currency_symbol'] ?? 'Rs.',
        ], $company?->id);

        return redirect()->route('settings.index', ['tab' => 'general'])
            ->with('success', 'General company settings and logo updated successfully.');
    }

    /**
     * Update feature toggles (Enable / Disable modules & attributes).
     */
    public function updateFeatures(Request $request): RedirectResponse
    {
        $companyId = CompanySettingService::resolveCompanyId();

        $featureKeys = [
            'sale_orders',
            'purchase_orders',
            'brands',
            'categories',
            'warehouses',
            'stock_transfers',
            'vouchers',
            'expenses',
            'day_book',
            'sale_returns',
            'purchase_returns',
        ];

        $settings = [];
        foreach ($featureKeys as $key) {
            $settings['features.'.$key] = $request->has('features_'.$key);
        }

        CompanySettingService::setMany($settings, $companyId);

        return redirect()->route('settings.index', ['tab' => 'features'])
            ->with('success', 'System feature toggles updated successfully.');
    }

    /**
     * Update thermal receipt and invoice template settings.
     */
    public function updateReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paper_size' => ['required', 'string', 'in:80mm,58mm,a4'],
            'header_text' => ['nullable', 'string', 'max:500'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'return_policy' => ['nullable', 'string', 'max:1000'],
        ]);

        $companyId = CompanySettingService::resolveCompanyId();

        CompanySettingService::setMany([
            'receipt.paper_size' => $validated['paper_size'],
            'receipt.show_logo' => $request->has('show_logo'),
            'receipt.show_customer_name' => $request->has('show_customer_name'),
            'receipt.show_cashier_name' => $request->has('show_cashier_name'),
            'receipt.show_tax_breakdown' => $request->has('show_tax_breakdown'),
            'receipt.show_barcode' => $request->has('show_barcode'),
            'receipt.show_qr_code' => $request->has('show_qr_code'),
            'receipt.show_sku' => $request->has('show_sku'),
            'receipt.header_text' => $validated['header_text'] ?? '',
            'receipt.footer_text' => $validated['footer_text'] ?? '',
            'receipt.return_policy' => $validated['return_policy'] ?? '',
        ], $companyId);

        return redirect()->route('settings.index', ['tab' => 'receipt'])
            ->with('success', 'Receipt template settings saved successfully.');
    }

    /**
     * Update default values & POS settings.
     */
    public function updateDefaults(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_customer_id' => ['nullable', 'exists:customers,id'],
            'default_vendor_id' => ['nullable', 'exists:vendors,id'],
            'default_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'default_payment_method' => ['nullable', 'string', 'in:cash,card,bank_transfer,credit'],
            'pos_allow_less_sale' => ['nullable', 'string', 'in:yes,no'],
            'pos_total_payable_type' => ['nullable', 'string', 'in:none,rounded,exact'],
            'pos_default_cursor' => ['nullable', 'string', 'in:search_box,barcode'],
            'pos_product_display' => ['nullable', 'string', 'in:image_view,list_view'],
            'pos_onscreen_keyboard' => ['nullable', 'string', 'in:enable,disable'],
            'pos_grocery_experience' => ['nullable', 'string', 'in:medicine,retail,grocery'],
            'pos_smtp_default' => ['nullable', 'string', 'in:yes,no'],
            'pos_sms_default' => ['nullable', 'string', 'in:yes,no'],
            'pos_whatsapp_default' => ['nullable', 'string', 'in:yes,no'],
            'pos_direct_cart' => ['nullable', 'string', 'in:yes,no'],
        ]);

        $companyId = CompanySettingService::resolveCompanyId();

        CompanySettingService::setMany([
            'defaults.customer_id' => $validated['default_customer_id'] ?? null,
            'defaults.vendor_id' => $validated['default_vendor_id'] ?? null,
            'defaults.warehouse_id' => $validated['default_warehouse_id'] ?? null,
            'defaults.payment_method' => $validated['default_payment_method'] ?? 'cash',
            'defaults.allow_zero_stock' => $request->has('allow_zero_stock'),

            'pos.allow_less_sale' => $validated['pos_allow_less_sale'] ?? 'no',
            'pos.total_payable_type' => $validated['pos_total_payable_type'] ?? 'none',
            'pos.default_cursor' => $validated['pos_default_cursor'] ?? 'search_box',
            'pos.product_display' => $validated['pos_product_display'] ?? 'image_view',
            'pos.onscreen_keyboard' => $validated['pos_onscreen_keyboard'] ?? 'disable',
            'pos.grocery_experience' => $validated['pos_grocery_experience'] ?? 'medicine',
            'pos.smtp_default' => $validated['pos_smtp_default'] ?? 'no',
            'pos.sms_default' => $validated['pos_sms_default'] ?? 'no',
            'pos.whatsapp_default' => $validated['pos_whatsapp_default'] ?? 'no',
            'pos.direct_cart' => $validated['pos_direct_cart'] ?? 'yes',
        ], $companyId);

        return redirect()->route('settings.index', ['tab' => 'defaults'])
            ->with('success', 'Default values and POS preferences saved.');
    }

    /**
     * Update branding and theme settings.
     */
    public function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'custom_css' => ['nullable', 'string'],
            'logo_light' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,ico', 'max:1024'],
        ]);

        $companyId = CompanySettingService::resolveCompanyId();

        // Handle file uploads
        if ($request->hasFile('logo_light')) {
            $path = $request->file('logo_light')->store('branding', 'public');
            CompanySettingService::set('branding.logo_light', $path, $companyId);
            if ($company = Company::find($companyId)) {
                $company->update(['logo' => $path]);
            }
        }
        if ($request->hasFile('logo_dark')) {
            $path = $request->file('logo_dark')->store('branding', 'public');
            CompanySettingService::set('branding.logo_dark', $path, $companyId);
            if ($company = Company::find($companyId)) {
                $company->update(['logo' => $path]);
            }
        }
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            CompanySettingService::set('branding.favicon', $path, $companyId);
        }

        CompanySettingService::setMany([
            'branding.app_name' => $validated['app_name'] ?? '',
            'branding.footer_text' => $validated['footer_text'] ?? '',
            'branding.accent_color' => $validated['accent_color'] ?? '#4f46e5',
            'branding.custom_css' => $validated['custom_css'] ?? '',
        ], $companyId);

        return redirect()->route('settings.index', ['tab' => 'branding'])
            ->with('success', 'Branding and theme settings updated successfully.');
    }
}
