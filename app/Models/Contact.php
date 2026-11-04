<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'countryId');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'contact_id');
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'contact_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'contact_id');
    }

    public function types()
    {
        return $this->belongsToMany(Type::class, 'contact_types', 'contact_id', 'type_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contact_products', 'contact_id', 'product_id');
    }
}
