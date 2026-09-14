<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define All Granular Module Permissions
        $permissionsList = [
            // Platform & Companies (Multi-Tenancy)
            ['name' => 'Manage All Companies', 'slug' => 'manage-all-companies', 'group' => 'Platform', 'description' => 'Can manage all tenant companies across the platform'],
            ['name' => 'Create Company', 'slug' => 'create-company', 'group' => 'Platform', 'description' => 'Can register and setup new tenant companies'],
            ['name' => 'View System Stats', 'slug' => 'view-system-stats', 'group' => 'Platform', 'description' => 'Can view platform-wide system statistics'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group' => 'Platform', 'description' => 'Can manage platform users'],
            ['name' => 'View Companies', 'slug' => 'companies.view', 'group' => 'Companies', 'description' => 'Can view list of tenant companies'],
            ['name' => 'Create Company (CRUD)', 'slug' => 'companies.create', 'group' => 'Companies', 'description' => 'Can register and setup new tenant companies'],
            ['name' => 'Edit Company', 'slug' => 'companies.edit', 'group' => 'Companies', 'description' => 'Can modify company information and settings'],
            ['name' => 'Delete Company', 'slug' => 'companies.delete', 'group' => 'Companies', 'description' => 'Can deactivate or delete companies'],

            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'Dashboard', 'description' => 'Can view business metrics and dashboard overview'],

            // POS Terminal
            ['name' => 'Access POS Terminal', 'slug' => 'pos.access', 'group' => 'POS Terminal', 'description' => 'Can open and use the point of sale terminal'],
            ['name' => 'Process POS Checkout', 'slug' => 'pos.checkout', 'group' => 'POS Terminal', 'description' => 'Can complete sales transactions in POS'],

            // Sales & Invoices
            ['name' => 'View Sales History', 'slug' => 'sales.view', 'group' => 'Sales & Invoices', 'description' => 'Can view list of sale invoices'],
            ['name' => 'Create Sale Invoice', 'slug' => 'sales.create', 'group' => 'Sales & Invoices', 'description' => 'Can create new manual sale invoices'],
            ['name' => 'View Sale Invoice Details', 'slug' => 'sales.show', 'group' => 'Sales & Invoices', 'description' => 'Can view invoice details and thermal receipts'],

            // Sale Orders (Bookings)
            ['name' => 'View Sale Orders', 'slug' => 'sale_orders.view', 'group' => 'Sale Orders', 'description' => 'Can view customer bookings/orders'],
            ['name' => 'Create Sale Order', 'slug' => 'sale_orders.create', 'group' => 'Sale Orders', 'description' => 'Can create new customer booking orders'],
            ['name' => 'Convert Sale Order', 'slug' => 'sale_orders.convert', 'group' => 'Sale Orders', 'description' => 'Can convert sale orders into sale invoices'],

            // Sale Returns
            ['name' => 'View Sale Returns', 'slug' => 'sale_returns.view', 'group' => 'Sale Returns', 'description' => 'Can view customer return history'],
            ['name' => 'Create Sale Return', 'slug' => 'sale_returns.create', 'group' => 'Sale Returns', 'description' => 'Can accept and process customer returns'],
            ['name' => 'View Sale Return Details', 'slug' => 'sale_returns.show', 'group' => 'Sale Returns', 'description' => 'Can view sale return vouchers'],

            // Purchases & Invoices
            ['name' => 'View Purchases', 'slug' => 'purchases.view', 'group' => 'Purchases', 'description' => 'Can view received vendor invoices'],
            ['name' => 'Create Purchase Invoice', 'slug' => 'purchases.create', 'group' => 'Purchases', 'description' => 'Can create new purchase invoices and add stock'],
            ['name' => 'View Purchase Details', 'slug' => 'purchases.show', 'group' => 'Purchases', 'description' => 'Can view purchase invoice vouchers'],

            // Purchase Orders
            ['name' => 'View Purchase Orders', 'slug' => 'purchase_orders.view', 'group' => 'Purchase Orders', 'description' => 'Can view supplier purchase orders'],
            ['name' => 'Create Purchase Order', 'slug' => 'purchase_orders.create', 'group' => 'Purchase Orders', 'description' => 'Can create new supplier purchase orders'],
            ['name' => 'Convert Purchase Order', 'slug' => 'purchase_orders.convert', 'group' => 'Purchase Orders', 'description' => 'Can convert purchase orders into invoices'],

            // Purchase Returns
            ['name' => 'View Purchase Returns', 'slug' => 'purchase_returns.view', 'group' => 'Purchase Returns', 'description' => 'Can view vendor return records'],
            ['name' => 'Create Purchase Return', 'slug' => 'purchase_returns.create', 'group' => 'Purchase Returns', 'description' => 'Can return goods to suppliers'],
            ['name' => 'View Purchase Return Details', 'slug' => 'purchase_returns.show', 'group' => 'Purchase Returns', 'description' => 'Can view vendor return details'],

            // Products & Inventory
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'Products', 'description' => 'Can view product list and pricing'],
            ['name' => 'Create Product', 'slug' => 'products.create', 'group' => 'Products', 'description' => 'Can add new products with units & barcodes'],
            ['name' => 'Edit Product', 'slug' => 'products.edit', 'group' => 'Products', 'description' => 'Can edit product details and secondary units'],
            ['name' => 'Delete Product', 'slug' => 'products.delete', 'group' => 'Products', 'description' => 'Can delete products from system'],
            ['name' => 'Print Product Barcodes', 'slug' => 'products.barcode', 'group' => 'Products', 'description' => 'Can generate and print product barcode labels'],

            // Categories
            ['name' => 'View Categories', 'slug' => 'categories.view', 'group' => 'Categories', 'description' => 'Can view product categories'],
            ['name' => 'Create Category', 'slug' => 'categories.create', 'group' => 'Categories', 'description' => 'Can create new categories'],
            ['name' => 'Edit Category', 'slug' => 'categories.edit', 'group' => 'Categories', 'description' => 'Can edit categories'],
            ['name' => 'Delete Category', 'slug' => 'categories.delete', 'group' => 'Categories', 'description' => 'Can delete categories'],

            // Units & Conversions
            ['name' => 'View Units', 'slug' => 'units.view', 'group' => 'Units', 'description' => 'Can view base and secondary units'],
            ['name' => 'Create Unit', 'slug' => 'units.create', 'group' => 'Units', 'description' => 'Can create new measurement units'],
            ['name' => 'Edit Unit', 'slug' => 'units.edit', 'group' => 'Units', 'description' => 'Can edit units and conversion factors'],
            ['name' => 'Delete Unit', 'slug' => 'units.delete', 'group' => 'Units', 'description' => 'Can delete units'],

            // Stock Management
            ['name' => 'View Stock Levels', 'slug' => 'stock.view', 'group' => 'Stock Management', 'description' => 'Can view stock inventory table'],
            ['name' => 'Adjust Stock', 'slug' => 'stock.adjust', 'group' => 'Stock Management', 'description' => 'Can perform physical inventory stock adjustments'],
            ['name' => 'View Stock Movements', 'slug' => 'stock.movements', 'group' => 'Stock Management', 'description' => 'Can audit complete stock movement audit logs'],

            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'Customers', 'description' => 'Can view customer directory'],
            ['name' => 'Create Customer', 'slug' => 'customers.create', 'group' => 'Customers', 'description' => 'Can register new customers'],
            ['name' => 'Edit Customer', 'slug' => 'customers.edit', 'group' => 'Customers', 'description' => 'Can update customer info'],
            ['name' => 'Delete Customer', 'slug' => 'customers.delete', 'group' => 'Customers', 'description' => 'Can delete customers'],

            // Vendors
            ['name' => 'View Vendors', 'slug' => 'vendors.view', 'group' => 'Vendors', 'description' => 'Can view vendor/supplier directory'],
            ['name' => 'Create Vendor', 'slug' => 'vendors.create', 'group' => 'Vendors', 'description' => 'Can register new suppliers'],
            ['name' => 'Edit Vendor', 'slug' => 'vendors.edit', 'group' => 'Vendors', 'description' => 'Can update vendor info'],
            ['name' => 'Delete Vendor', 'slug' => 'vendors.delete', 'group' => 'Vendors', 'description' => 'Can delete vendors'],

            // Day Book / Cash Book & Ledgers
            ['name' => 'View Day Book', 'slug' => 'day_book.view', 'group' => 'Ledgers (Khata)', 'description' => 'Can view daily cash book register and closing drawer summary'],
            ['name' => 'Create Day Book Opening', 'slug' => 'day_book.create', 'group' => 'Ledgers (Khata)', 'description' => 'Can enter daily initial counter opening balance'],
            ['name' => 'View Customer Ledgers', 'slug' => 'ledgers.customer', 'group' => 'Ledgers (Khata)', 'description' => 'Can view customer accounts and outstanding balances'],
            ['name' => 'View Vendor Ledgers', 'slug' => 'ledgers.vendor', 'group' => 'Ledgers (Khata)', 'description' => 'Can view supplier accounts and payable balances'],
            ['name' => 'View Vouchers', 'slug' => 'vouchers.view', 'group' => 'Vouchers', 'description' => 'Can view cash receipt and payment vouchers'],
            ['name' => 'Create Voucher', 'slug' => 'vouchers.create', 'group' => 'Vouchers', 'description' => 'Can record customer cash receipts and vendor payments'],

            // Expenses & Expense Categories
            ['name' => 'View Expenses', 'slug' => 'expenses.view', 'group' => 'Expenses', 'description' => 'Can view list of business expenses'],
            ['name' => 'Create Expense', 'slug' => 'expenses.create', 'group' => 'Expenses', 'description' => 'Can record new business expenses'],
            ['name' => 'Edit Expense', 'slug' => 'expenses.edit', 'group' => 'Expenses', 'description' => 'Can modify recorded expenses'],
            ['name' => 'Delete Expense', 'slug' => 'expenses.delete', 'group' => 'Expenses', 'description' => 'Can delete recorded expenses'],

            // Analytics & Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'Reports', 'description' => 'Can view profit/loss and sales analytics'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'User Management', 'description' => 'Can view list of system users'],
            ['name' => 'Create User', 'slug' => 'users.create', 'group' => 'User Management', 'description' => 'Can add new system operators'],
            ['name' => 'Edit User', 'slug' => 'users.edit', 'group' => 'User Management', 'description' => 'Can edit user details and change roles'],
            ['name' => 'Delete User', 'slug' => 'users.delete', 'group' => 'User Management', 'description' => 'Can delete or deactivate user accounts'],

            // Roles Management
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'Roles & Matrix', 'description' => 'Can view roles and permissions matrix'],
            ['name' => 'Create Role', 'slug' => 'roles.create', 'group' => 'Roles & Matrix', 'description' => 'Can define new custom roles and matrix presets'],
            ['name' => 'Edit Role Permissions', 'slug' => 'roles.edit', 'group' => 'Roles & Matrix', 'description' => 'Can modify permissions assigned to roles'],
            ['name' => 'Delete Role', 'slug' => 'roles.delete', 'group' => 'Roles & Matrix', 'description' => 'Can remove custom roles'],

            // Permissions Manager
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'group' => 'Permissions Manager', 'description' => 'Can view full permissions list & modules'],
            ['name' => 'Create Permission', 'slug' => 'permissions.create', 'group' => 'Permissions Manager', 'description' => 'Can register new capability slugs & modules'],
            ['name' => 'Edit Permission', 'slug' => 'permissions.edit', 'group' => 'Permissions Manager', 'description' => 'Can modify permission metadata and groups'],
            ['name' => 'Delete Permission', 'slug' => 'permissions.delete', 'group' => 'Permissions Manager', 'description' => 'Can delete custom permissions from system'],
        ];

        foreach ($permissionsList as $p) {
            Permission::firstOrCreate(
                ['name' => $p['slug'], 'guard_name' => 'web'],
                [
                    'slug' => $p['slug'],
                    'group' => $p['group'],
                    'description' => $p['description'],
                ]
            );
        }

        // 2. Create System Roles
        $ownerRole = Role::firstOrCreate(
            ['name' => 'Owner', 'guard_name' => 'web'],
            [
                'slug' => 'owner',
                'description' => 'Level 1 System Owner with full multi-tenant platform administration rights.',
                'is_system' => true,
            ]
        );

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            [
                'slug' => 'super-admin',
                'description' => 'Level 2 Company Admin with full control over their tenant company data, users, and settings.',
                'is_system' => true,
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin / Manager', 'guard_name' => 'web'],
            [
                'slug' => 'admin',
                'description' => 'General business operations manager with access to inventory, sales, purchases, and reporting.',
                'is_system' => true,
            ]
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => 'Cashier', 'guard_name' => 'web'],
            [
                'slug' => 'cashier',
                'description' => 'Front-counter sales operator restricted to POS terminal, customer management, and sale receipts.',
                'is_system' => false,
            ]
        );

        $inventoryRole = Role::firstOrCreate(
            ['name' => 'Inventory Manager', 'guard_name' => 'web'],
            [
                'slug' => 'inventory-manager',
                'description' => 'Responsible for stocks, goods receiving purchases, supplier orders, and barcode generation.',
                'is_system' => false,
            ]
        );

        // 3. Assign Permissions to Roles
        $allPermissions = Permission::all();
        $ownerRole->syncPermissions($allPermissions);
        $superAdminRole->syncPermissions($allPermissions);

        // Admin permissions
        $adminPermissions = Permission::whereNotIn('name', [
            'roles.create', 'roles.delete', 'users.delete', 'manage-all-companies', 'create-company',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Cashier permissions
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view', 'pos.access', 'pos.checkout', 'sales.view', 'sales.create', 'sales.show',
            'sale_orders.view', 'sale_orders.create', 'sale_orders.convert', 'sale_returns.view',
            'sale_returns.create', 'sale_returns.show', 'customers.view', 'customers.create', 'products.view',
        ])->get();
        $cashierRole->syncPermissions($cashierPermissions);

        // Inventory Manager permissions
        $inventoryPermissions = Permission::whereIn('name', [
            'dashboard.view', 'products.view', 'products.create', 'products.edit', 'products.barcode',
            'categories.view', 'categories.create', 'categories.edit', 'units.view', 'units.create',
            'units.edit', 'stock.view', 'stock.adjust', 'stock.movements', 'purchases.view',
            'purchases.create', 'purchases.show', 'purchase_orders.view', 'purchase_orders.create',
            'purchase_orders.convert', 'purchase_returns.view', 'purchase_returns.create',
            'purchase_returns.show', 'vendors.view', 'vendors.create', 'vendors.edit',
        ])->get();
        $inventoryRole->syncPermissions($inventoryPermissions);

        // 4. Create Level 1 System Owner User (Not attached to any company)
        $owner = User::updateOrCreate(
            ['email' => 'owner@saasplatform.com'],
            [
                'name' => 'System Owner',
                'password' => Hash::make('password123'),
                'company_id' => null, // Owner has no company_id (Level 1)
                'is_active' => true,
            ]
        );
        $owner->syncRoles([$ownerRole]);

        $ownerAlias = User::updateOrCreate(
            ['email' => 'owner@smartpos.com'],
            [
                'name' => 'System Owner',
                'password' => Hash::make('password123'),
                'company_id' => null,
                'is_active' => true,
            ]
        );
        $ownerAlias->syncRoles([$ownerRole]);

        // 5. Create Default Level 2 Tenant Company
        $defaultCompany = Company::firstOrCreate(
            ['name' => 'Smart POS General Trading LLC'],
            [
                'code' => 'COMP-001',
                'email' => 'contact@smartpos.com',
                'phone' => '+92 300 1234567',
                'address' => 'Main Commercial Boulevard, Suite 100',
                'currency' => 'PKR',
                'is_active' => true,
            ]
        );

        // 6. Create Level 2 Super Admin User (Belongs to Company 1)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@smartpos.com'],
            [
                'name' => 'Super Admin',
                'company_id' => $defaultCompany->id,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        // 7. Create Company Cashier User (Belongs to Company 1)
        $cashier = User::updateOrCreate(
            ['email' => 'cashier@smartpos.com'],
            [
                'name' => 'Afeera Cashier',
                'company_id' => $defaultCompany->id,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $cashier->syncRoles([$cashierRole]);

        // 8. Create Default Expense Categories for tenant company
        $defaultCategories = [
            ['name' => 'Shop Rent', 'description' => 'Monthly property or building lease rent'],
            ['name' => 'Utilities & Electricity', 'description' => 'Electricity, water, and gas bills'],
            ['name' => 'Staff Salaries', 'description' => 'Employee monthly payroll & wages'],
            ['name' => 'Tea & Refreshment', 'description' => 'Daily office snacks, tea, and entertainment'],
            ['name' => 'Maintenance & Repairs', 'description' => 'Store equipment and shop maintenance'],
            ['name' => 'Transport & Logistics', 'description' => 'Goods transport, fuel, and delivery charges'],
            ['name' => 'Marketing & Ads', 'description' => 'Promotional flyers, social media ads'],
            ['name' => 'Miscellaneous', 'description' => 'Other minor operational costs'],
        ];

        foreach ($defaultCategories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['company_id' => $defaultCompany->id, 'name' => $cat['name']],
                ['description' => $cat['description'], 'is_active' => true]
            );
        }
    }
}
