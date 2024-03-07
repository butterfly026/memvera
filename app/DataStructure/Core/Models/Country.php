<?php

namespace App\DataStructure\Core\Models;

use App\DataStructure\Core\Contracts\Country as CountryContract;
use Illuminate\Database\Eloquent\Model;

class Country extends Model implements CountryContract
{
    public $timestamps = false;
}
