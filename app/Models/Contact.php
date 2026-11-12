<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = [
        'typeIds',
        'productIds',
        'codeMeta',
        'companyId',
        'countryId',
        'bankAccounts',
    ];

    public function getCodeMetaAttribute()
    {
        return $this->attributes['code_meta'] ?? null;
    }

    public function setCodeMetaAttribute($value)
    {
        $this->attributes['code_meta'] = $value;
    }

    public function getCompanyIdAttribute()
    {
        return $this->attributes['company_id'] ?? null;
    }

    public function setCompanyIdAttribute($value)
    {
        $this->attributes['company_id'] = $value;
    }

    public function getCountryIdAttribute()
    {
        return $this->attributes['country_id'] ?? null;
    }

    public function setCountryIdAttribute($value)
    {
        $this->attributes['country_id'] = $value;
    }

    public function getTypeIdsAttribute()
    {
        if (!$this->id) {
            return [];
        }
        try {
            return DB::table('contact_type_ids')->where('contact_id', $this->id)->pluck('type_ids')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProductIdsAttribute()
    {
        if (!$this->id) {
            return [];
        }
        try {
            return DB::table('contact_product_ids')->where('contact_id', $this->id)->pluck('product_ids')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'contacts_addresses', 'contact_id', 'addresses_id');
    }

    public function getRepresentativesAttribute()
    {
        return $this->addresses ? $this->addresses->flatMap->representatives : collect();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contact_product_ids', 'contact_id', 'product_ids');
    }

    public function types()
    {
        return $this->belongsToMany(Type::class, 'contact_type_ids', 'contact_id', 'type_ids');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'contact_id');
    }

    public function getContractsAttribute()
    {
        $dealIds = $this->deals()->pluck('id');
        return Contract::whereIn('purchase_id', $dealIds)->orWhereIn('sale_id', $dealIds)->get();
    }

    public function getBankAccountsAttribute()
    {
        return $this->company ? $this->company->bankAccounts : collect();
    }
}
