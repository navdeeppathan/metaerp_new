<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerContract extends Model
{
    protected $table = 'buyercontracts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'contact_id',
        'contact_person_id',
        'shipping_address_id',
        'version',
        'contract_id',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(Representative::class, 'contact_person_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }
}
