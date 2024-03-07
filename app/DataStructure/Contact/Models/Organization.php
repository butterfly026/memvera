<?php

namespace App\DataStructure\Contact\Models;

use App\DataStructure\Contact\Contracts\Organization as OrganizationContract;
use App\Traits\Attribute\CustomAttribute;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model implements OrganizationContract
{
    use CustomAttribute;

    protected $casts = [
        'address' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
    ];
}
