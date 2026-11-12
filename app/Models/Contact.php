<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'code_meta',
        'company_id',
        'country_id',
        'currency',
        'initials',
        'name',
        'registration',
        'vat',
        'additional_information',
        'eori_number',
        'website',
        'language_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'contacts_addresses', 'contact_id', 'addresses_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contact_product_ids', 'contact_id', 'product_ids');
    }

    public function types()
    {
        return $this->belongsToMany(Type::class, 'contact_type_ids', 'contact_id', 'type_ids');
    }

    public function directProducts()
    {
        return $this->hasMany(Product::class, 'contact_id', 'id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'contact_id', 'id');
    }

    public function buyerContracts()
    {
        return $this->hasMany(BuyerContract::class, 'contact_id', 'id');
    }

    public function sellerContracts()
    {
        return $this->hasMany(SellerContract::class, 'contact_id', 'id');
    }
}
