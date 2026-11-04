<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $guarded = [];
    protected $casts = [
        'vat' => 'double',
        'active' => 'boolean',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'country_id');
    }
}
