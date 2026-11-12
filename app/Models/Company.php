<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'code',
        'company_number',
        'country_id',
        'email',
        'fax_number',
        'footer',
        'logo_url',
        'name',
        'phone_number',
        'vat_number',
        'web_site',
        'address_id',
        'country_company_id',
        'domain_name',
        'eori_number',
        'footer_file',
        'logo_file',
        'stamp_file',
        'sales_invoice_number',
        'logo_name',
        'footer_name',
        'stamp_name',
        'archive_footer_id',
        'archive_logo_id',
        'archive_stamp_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function countryCompany()
    {
        return $this->belongsTo(Country::class, 'country_company_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function archiveFooter()
    {
        return $this->belongsTo(Archive::class, 'archive_footer_id', 'id');
    }

    public function archiveLogo()
    {
        return $this->belongsTo(Archive::class, 'archive_logo_id', 'id');
    }

    public function archiveStamp()
    {
        return $this->belongsTo(Archive::class, 'archive_stamp_id', 'id');
    }

    public function bankAccounts()
    {
        return $this->belongsToMany(BankAccount::class, 'companies_bank_accounts', 'company_id', 'bank_accounts_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'company_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'company_id', 'id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'meta_company_id', 'id');
    }

    public function representatives()
    {
        return $this->belongsToMany(Representative::class, 'addresses_representatives', 'address_id', 'representatives_id')
            ->whereRaw('1 = 0');
    }

    public function getRepresentativesAttribute()
    {
        if ($this->relationLoaded('address') && $this->address) {
            return $this->address->representatives ?? collect([]);
        }
        if ($this->address_id) {
            $addr = Address::with('representatives')->find($this->address_id);
            return $addr ? ($addr->representatives ?? collect([])) : collect([]);
        }
        return collect([]);
    }
}
