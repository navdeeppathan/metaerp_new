<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
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
        'version' => 'integer',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'country_id', 'id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'country_id', 'id');
    }

    public function companyCountries()
    {
        return $this->hasMany(Company::class, 'country_company_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'country_id', 'id');
    }

    public function originDeals()
    {
        return $this->hasMany(Deal::class, 'country_of_origin_id', 'id');
    }

    public function templates()
    {
        return $this->hasMany(Template::class, 'country_id', 'id');
    }
}
