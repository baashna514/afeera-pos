<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'return_number',
        'sale_id',
        'customer_id',
        'return_date',
        'total_amount',
        'refund_amount',
        'payment_status',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
