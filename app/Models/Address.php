<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'address_line1',
        'address_line2',
        'address_name',
        'city',
        'for_google_maps_search',
        'postal_box',
        'postal_code',
        'state',
        'version',
        'country_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function representatives()
    {
        return $this->belongsToMany(Representative::class, 'addresses_representatives', 'address_id', 'representatives_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contacts_addresses', 'addresses_id', 'contact_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'address_id', 'id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'address_id', 'id');
    }

    public function shippingDeals()
    {
        return $this->hasMany(Deal::class, 'shipping_address_id', 'id');
    }

    public function buyerContracts()
    {
        return $this->hasMany(BuyerContract::class, 'shipping_address_id', 'id');
    }

    public function sellerContracts()
    {
        return $this->hasMany(SellerContract::class, 'shipping_address_id', 'id');
    }
}
