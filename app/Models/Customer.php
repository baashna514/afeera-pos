<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
