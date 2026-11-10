<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
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

    public function getAddressLine1Attribute() { return $this->attributes['address_line1'] ?? null; }
    public function setAddressLine1Attribute($val) { $this->attributes['address_line1'] = $val; }

    public function getAddressLine2Attribute() { return $this->attributes['address_line2'] ?? null; }
    public function setAddressLine2Attribute($val) { $this->attributes['address_line2'] = $val; }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function representatives()
    {
        return $this->belongsToMany(Representative::class, 'addresses_representatives', 'address_id', 'representatives_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contacts_addresses', 'addresses_id', 'contact_id');
    }
}
