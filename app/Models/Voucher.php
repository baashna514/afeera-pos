<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'voucher_number',
        'type',
        'customer_id',
        'vendor_id',
        'voucher_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'receipt' ? 'Cash Receipt (Customer)' : 'Cash Payment (Vendor)';
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'receipt'
            ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
            : 'bg-blue-100 text-blue-800 border-blue-300';
    }

    public function getPartyNameAttribute(): string
    {
        if ($this->type === 'receipt') {
            return $this->customer ? $this->customer->name : 'Walk-in Customer';
        }

        return $this->vendor ? $this->vendor->name : 'Vendor';
    }
}
