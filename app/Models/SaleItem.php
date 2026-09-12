<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'sale_id',
        'product_id',
        'unit_id',
        'conversion_rate',
        'quantity',
        'base_quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'conversion_rate' => 'decimal:4',
        'base_quantity' => 'decimal:4',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
