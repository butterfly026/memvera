<?php

namespace App\DataStructure\Product\Models;

use App\DataStructure\Product\Contracts\Product as ProductContract;
use App\Traits\Attribute\CustomAttribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements ProductContract
{
    use CustomAttribute;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'quantity',
        'price'
    ];
}
