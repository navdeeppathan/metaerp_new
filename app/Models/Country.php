<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'alpha2',
        'alpha3',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'intermediate_region',
        'name',
        'region',
        'sub_region',
        'vat',
        'version',
    ];

    protected $casts = [
        'active' => 'boolean',
        'vat' => 'float',
    ];

    // Accessors and mutators to support both camelCase and snake_case
    public function getCurrencyCodeAttribute() { return $this->attributes['currency_code'] ?? null; }
    public function setCurrencyCodeAttribute($val) { $this->attributes['currency_code'] = $val; }

    public function getCurrencyNameAttribute() { return $this->attributes['currency_name'] ?? null; }
    public function setCurrencyNameAttribute($val) { $this->attributes['currency_name'] = $val; }

    public function getCurrencySymbolAttribute() { return $this->attributes['currency_symbol'] ?? null; }
    public function setCurrencySymbolAttribute($val) { $this->attributes['currency_symbol'] = $val; }

    public function getSubRegionAttribute() { return $this->attributes['sub_region'] ?? null; }
    public function setSubRegionAttribute($val) { $this->attributes['sub_region'] = $val; }

    public function getIntermediateRegionAttribute() { return $this->attributes['intermediate_region'] ?? null; }
    public function setIntermediateRegionAttribute($val) { $this->attributes['intermediate_region'] = $val; }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'country_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'country_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'country_id');
    }
}
