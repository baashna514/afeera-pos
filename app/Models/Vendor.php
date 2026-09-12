<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
