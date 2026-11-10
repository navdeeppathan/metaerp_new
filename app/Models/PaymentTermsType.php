<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTermsType extends Model
{
    protected $table = 'payment_terms_type';
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
        if (array_key_exists('name', $this->attributes) && !array_key_exists('description', $this->attributes)) {
            $this->attributes['name'] = $value;
        } else {
            $this->attributes['description'] = $value;
        }
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? $this->attributes['name'] ?? null;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value;
    }
}
