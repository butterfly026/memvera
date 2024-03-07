<?php

namespace App\DataStructure\Lead\Models;

use App\DataStructure\Lead\Contracts\Source as SourceContract;
use Illuminate\Database\Eloquent\Model;

class Source extends Model implements SourceContract
{
    protected $table = 'lead_sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Get the leads.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass());
    }
}
