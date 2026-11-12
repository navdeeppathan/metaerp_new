<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressRepresentative extends Model
{
    protected $table = 'addresses_representatives';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'address_id',
        'representatives_id',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function representative()
    {
        return $this->belongsTo(Representative::class, 'representatives_id', 'id');
    }
}
