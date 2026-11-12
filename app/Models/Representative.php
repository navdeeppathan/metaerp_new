<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Representative extends Model
{
    protected $table = 'representatives';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'name',
        'phone',
        'type',
        'job_title',
        'mobile',
        'phone2',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'addresses_representatives', 'representatives_id', 'address_id');
    }

    public function metaDeals()
    {
        return $this->hasMany(Deal::class, 'meta_representative_id', 'id');
    }

    public function contactDeals()
    {
        return $this->hasMany(Deal::class, 'contact_representative_id', 'id');
    }

    public function buyerContracts()
    {
        return $this->hasMany(BuyerContract::class, 'contact_person_id', 'id');
    }

    public function sellerContracts()
    {
        return $this->hasMany(SellerContract::class, 'contact_person_id', 'id');
    }
}
