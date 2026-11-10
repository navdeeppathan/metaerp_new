<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getCompanyNumberAttribute() { return $this->attributes['company_number'] ?? null; }
    public function setCompanyNumberAttribute($val) { $this->attributes['company_number'] = $val; }

    public function getVatNumberAttribute() { return $this->attributes['vat'] ?? null; }
    public function setVatNumberAttribute($val) { $this->attributes['vat'] = $val; }

    public function getPhoneNumberAttribute() { return $this->attributes['phone_number'] ?? null; }
    public function setPhoneNumberAttribute($val) { $this->attributes['phone_number'] = $val; }

    public function getAddressLine1Attribute() { return $this->attributes['line1'] ?? $this->attributes['address_line1'] ?? null; }
    public function setAddressLine1Attribute($val) { $this->attributes['address_line1'] = $val; }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id')->withDefault([
            'address_line1' => $this->line1 ?? $this->address_line1 ?? '',
            'city' => $this->city ?? '',
            'country' => $this->country ?? null,
        ]);
    }

    public function bankAccounts()
    {
        return $this->belongsToMany(BankAccount::class, 'companies_bank_accounts', 'company_id', 'bank_accounts_id');
    }

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'company_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'meta_company_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'company_id');
    }
}
