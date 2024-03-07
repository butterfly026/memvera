<?php

namespace App\DataStructure\Core\Models;

use App\DataStructure\Core\Contracts\CountryState as CountryStateContract;
use Illuminate\Database\Eloquent\Model;

class CountryState extends Model implements CountryStateContract
{
    public $timestamps = false;
}
