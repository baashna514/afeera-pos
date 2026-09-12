<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
