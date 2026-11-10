<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['description'] ?? $this->attributes['name'] ?? null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['description'] = $value;
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? $this->attributes['name'] ?? null;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['description'] = $value;
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'department_id');
    }
}
