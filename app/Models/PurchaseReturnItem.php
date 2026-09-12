<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'purchase_return_id',
        'product_id',
        'unit_id',
        'conversion_rate',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:4',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
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
