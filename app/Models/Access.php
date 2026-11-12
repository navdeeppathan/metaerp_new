<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'access';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'access_type',
        'active',
        'module',
        'version',
        'accessgroup_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function accessGroup()
    {
        return $this->belongsTo(AccessGroup::class, 'accessgroup_id', 'id');
    }

    public function getAccessTypeAttribute()
    {
        return $this->attributes['access_type'] ?? null;
    }

    public function setAccessTypeAttribute($value)
    {
        $this->attributes['access_type'] = $value;
    }
}

