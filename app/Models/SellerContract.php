<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerContract extends Model
{
    protected $table = 'sellercontracts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'contact_id',
        'contact_person_id',
        'shipping_address_id',
        'version',
        'contract_id',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(Representative::class, 'contact_person_id', 'id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }

    public function productContracts()
    {
        return $this->hasMany(ProductContract::class, 'sellercontract_id', 'id');
    }
}
