<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

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

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'contact_id');
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

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'contact_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'contact_id');
    }
}
