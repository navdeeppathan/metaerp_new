<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessGroup extends Model
{
    protected $table = 'accessgroups';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function accessList()
    {
        return $this->hasMany(Access::class, 'accessgroup_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'accessgroup_id');
    }
}
