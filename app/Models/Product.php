<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'barcode',
        'sku',
        'category_id',
        'brand_id',
        'unit_id',
        'default_sale_unit_id',
        'default_purchase_unit_id',
        'purchase_price',
        'selling_price',
        'quantity',
        'alert_quantity',
        'default_discount_type',
        'default_discount_value',
        'description',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'default_discount_value' => 'decimal:2',
        'quantity' => 'integer',
        'alert_quantity' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function defaultSaleUnit()
    {
        return $this->belongsTo(Unit::class, 'default_sale_unit_id');
    }

    public function defaultPurchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'default_purchase_unit_id');
    }

    public function secondaryUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function getQuantityInWarehouse(?int $warehouseId): int
    {
        if (! $warehouseId) {
            return (int) $this->quantity;
        }

        $ws = $this->warehouseStocks->firstWhere('warehouse_id', $warehouseId);

        return $ws ? (int) $ws->quantity : 0;
    }

    /**
     * Get all available units (Base Unit + Secondary Units) with their conversion rates & prices.
     */
    public function getAvailableUnitsAttribute(): array
    {
        $units = [];

        if ($this->unit) {
            $units[] = [
                'unit_id' => $this->unit->id,
                'name' => $this->unit->name,
                'short_code' => $this->unit->short_code,
                'conversion_rate' => 1.0,
                'operator' => 'multiply',
                'raw_rate' => 1.0,
                'sale_price' => (float) $this->selling_price,
                'purchase_price' => (float) $this->purchase_price,
                'is_base' => true,
            ];
        }

        foreach ($this->secondaryUnits as $su) {
            if ($su->unit) {
                $rawRate = (float) ($su->conversion_rate ?? 1.0);
                $op = $su->operator ?? 'multiply';

                // Effective multiplier to convert quantity of this unit into base unit quantity
                $effectiveMultiplier = ($op === 'divide') ? ($rawRate > 0 ? (1.0 / $rawRate) : 1.0) : $rawRate;

                // Price calculation: use explicit price if set, otherwise calculate based on operator
                $salePrice = (float) ($su->sale_price ?? 0);
                if ($salePrice <= 0) {
                    $salePrice = ($op === 'divide')
                        ? ($rawRate > 0 ? round((float) $this->selling_price / $rawRate, 2) : (float) $this->selling_price)
                        : round((float) $this->selling_price * $rawRate, 2);
                }

                $purchasePrice = (float) ($su->purchase_price ?? 0);
                if ($purchasePrice <= 0) {
                    $purchasePrice = ($op === 'divide')
                        ? ($rawRate > 0 ? round((float) $this->purchase_price / $rawRate, 2) : (float) $this->purchase_price)
                        : round((float) $this->purchase_price * $rawRate, 2);
                }

                $units[] = [
                    'unit_id' => $su->unit->id,
                    'name' => $su->unit->name,
                    'short_code' => $su->unit->short_code,
                    'conversion_rate' => $effectiveMultiplier,
                    'operator' => $op,
                    'raw_rate' => $rawRate,
                    'sale_price' => $salePrice,
                    'purchase_price' => $purchasePrice,
                    'is_base' => false,
                ];
            }
        }

        return $units;
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'alert_quantity');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->alert_quantity;
    }
}
