<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactAddress extends Model
{
    protected $table = 'contacts_addresses';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contact_id',
        'addresses_id',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'addresses_id', 'id');
    }
}
