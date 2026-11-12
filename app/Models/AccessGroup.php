<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessGroup extends Model
{
    protected $table = 'accessgroups';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'name',
        'version',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function accessList()
    {
        return $this->hasMany(Access::class, 'accessgroup_id', 'id');
    }

    public function accesses()
    {
        return $this->accessList();
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'accessgroup_id', 'id');
    }
}
