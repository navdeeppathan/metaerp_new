<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'types';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'active',
        'version',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = [
        'description',
    ];

    public function getNameAttribute($value)
    {
        return $value ?? $this->attributes['description'] ?? null;
    }

    public function getDescriptionAttribute($value)
    {
        return $this->attributes['description'] ?? $this->attributes['name'] ?? null;
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_type_ids', 'type_ids', 'contact_id');
    }
}
