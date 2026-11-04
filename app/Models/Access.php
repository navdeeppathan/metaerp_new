<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'access';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function accessGroup()
    {
        return $this->belongsTo(AccessGroup::class, 'accessgroup_id');
    }
}
