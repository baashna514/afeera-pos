<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayBook extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'date',
        'opening_balance',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_balance' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
