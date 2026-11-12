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
        return $this->hasMany(ContactType::class, 'contact_id', 'id');
    }

    public function getTypeIdsAttribute()
    {
        if ($this->relationLoaded('types')) {
            return $this->types->pluck('type_ids')->filter()->values()->all();
        }
        return \Illuminate\Support\Facades\DB::table('contact_type_ids')
            ->where('contact_id', $this->id)
            ->pluck('type_ids')
            ->filter()
            ->values()
            ->all();
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

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'contact_id', 'id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function getBankAccountsAttribute()
    {
        return collect([]);
    }

    public function getCodeMetaAttribute()
    {
        return $this->attributes['code_meta'] ?? null;
    }

    public function setCodeMetaAttribute($value)
    {
        $this->attributes['code_meta'] = $value;
    }

    public function getAdditionalInformationAttribute()
    {
        return $this->attributes['additional_information'] ?? null;
    }

    public function setAdditionalInformationAttribute($value)
    {
        $this->attributes['additional_information'] = $value;
    }

    public function getEoriNumberAttribute()
    {
        return $this->attributes['eori_number'] ?? null;
    }

    public function setEoriNumberAttribute($value)
    {
        $this->attributes['eori_number'] = $value;
    }
}

