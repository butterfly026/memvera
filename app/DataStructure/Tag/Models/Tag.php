<?php

namespace App\DataStructure\Tag\Models;

use App\DataStructure\Tag\Contracts\Tag as TagContract;
use App\DataStructure\User\Models\UserProxy;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model implements TagContract
{
    protected $table = 'tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'color',
        'user_id',
    ];

    /**
     * Get the user that owns the tag.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
