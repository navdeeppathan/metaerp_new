<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected static ?array $columnsCache = null;

    protected $fillable = [
        'name',
        'code',
        'email',
        'active',
        'version',
        // Company Number
        'company_number',
        'companyNumber',
        // VAT Number
        'vat_number',
        'vatNumber',
        // Phone Number
        'phone_number',
        'phoneNumber',
        // Fax Number
        'fax_number',
        'faxNumber',
        // Website
        'web_site',
        'webSite',
        'website',
        // EORI Number
        'eori_number',
        'eoriNumber',
        // Country
        'country_id',
        'countryId',
        'country_company_id',
        // Domain
        'domain',
        'domain_name',
        'domainName',
        // Logo & Footer & Stamp
        'logo_url',
        'logoUrl',
        'logo_file',
        'logoFile',
        'logo_name',
        'logoName',
        'footer',
        'footer_file',
        'footerFile',
        'footer_name',
        'footerName',
        'stamp_file',
        'stampFile',
        'stamp_name',
        'stampName',
        'stampUrl',
        'archive_footer_id',
        'archiveFooterId',
        'archive_logo_id',
        'archiveLogoId',
        'archive_stamp_id',
        'archiveStampId',
        'address_id',
        'sales_invoice_number',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    private function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_null($value)) {
            return false;
        }
        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }
        if (is_string($value)) {
            if ($value === "\x01" || $value === "\x00" || $value === "\0" || $value === chr(1) || $value === chr(0)) {
                return ord($value) === 1;
            }
            $trimmed = strtolower(trim($value));
            return in_array($trimmed, ['1', 'true', 'active', 'on', 'yes'], true);
        }
        return (bool) $value;
    }

    public function getActiveAttribute($value): bool
    {
        return $this->parseBooleanValue($value ?? ($this->attributes['active'] ?? null));
    }

    public function setActiveAttribute($value): void
    {
        $this->attributes['active'] = $this->parseBooleanValue($value) ? 1 : 0;
    }

    public function getTableColumns(): array
    {
        if (static::$columnsCache === null) {
            try {
                static::$columnsCache = Schema::getColumnListing($this->getTable());
            } catch (\Throwable $e) {
                static::$columnsCache = [];
            }
        }
        return static::$columnsCache;
    }

    public function hasTableColumn(string $column): bool
    {
        return in_array($column, $this->getTableColumns(), true);
    }

    public function getCompanyNumberAttribute()
    {
        return $this->attributes['company_number']
            ?? $this->attributes['companyNumber']
            ?? null;
    }

    public function setCompanyNumberAttribute($value)
    {
        if ($this->hasTableColumn('company_number')) {
            $this->attributes['company_number'] = $value;
        }
        if ($this->hasTableColumn('companyNumber')) {
            $this->attributes['companyNumber'] = $value;
        }
    }

    public function getVatNumberAttribute()
    {
        return $this->attributes['vat_number']
            ?? $this->attributes['vatNumber']
            ?? null;
    }

    public function setVatNumberAttribute($value)
    {
        if ($this->hasTableColumn('vat_number')) {
            $this->attributes['vat_number'] = $value;
        }
        if ($this->hasTableColumn('vatNumber')) {
            $this->attributes['vatNumber'] = $value;
        }
    }

    public function getPhoneNumberAttribute()
    {
        return $this->attributes['phone_number']
            ?? $this->attributes['phoneNumber']
            ?? null;
    }

    public function setPhoneNumberAttribute($value)
    {
        if ($this->hasTableColumn('phone_number')) {
            $this->attributes['phone_number'] = $value;
        }
        if ($this->hasTableColumn('phoneNumber')) {
            $this->attributes['phoneNumber'] = $value;
        }
    }

    public function getFaxNumberAttribute()
    {
        return $this->attributes['fax_number']
            ?? $this->attributes['faxNumber']
            ?? null;
    }

    public function setFaxNumberAttribute($value)
    {
        if ($this->hasTableColumn('fax_number')) {
            $this->attributes['fax_number'] = $value;
        }
        if ($this->hasTableColumn('faxNumber')) {
            $this->attributes['faxNumber'] = $value;
        }
    }

    public function getWebSiteAttribute()
    {
        return $this->attributes['web_site']
            ?? $this->attributes['webSite']
            ?? $this->attributes['website']
            ?? null;
    }

    public function setWebSiteAttribute($value)
    {
        if ($this->hasTableColumn('web_site')) {
            $this->attributes['web_site'] = $value;
        }
        if ($this->hasTableColumn('webSite')) {
            $this->attributes['webSite'] = $value;
        }
    }

    public function getEoriNumberAttribute()
    {
        return $this->attributes['eori_number']
            ?? $this->attributes['eoriNumber']
            ?? null;
    }

    public function setEoriNumberAttribute($value)
    {
        if ($this->hasTableColumn('eori_number')) {
            $this->attributes['eori_number'] = $value;
        }
        if ($this->hasTableColumn('eoriNumber')) {
            $this->attributes['eoriNumber'] = $value;
        }
    }

    public function getDomainAttribute()
    {
        return $this->attributes['domain']
            ?? $this->attributes['domain_name']
            ?? $this->attributes['domainName']
            ?? null;
    }

    public function setDomainAttribute($value)
    {
        if ($this->hasTableColumn('domain')) {
            $this->attributes['domain'] = $value;
        }
        if ($this->hasTableColumn('domain_name')) {
            $this->attributes['domain_name'] = $value;
        }
    }

    public function getDomainNameAttribute()
    {
        return $this->getDomainAttribute();
    }

    public function setDomainNameAttribute($value)
    {
        $this->setDomainAttribute($value);
    }

    public function getCountryIdAttribute()
    {
        return $this->attributes['country_id']
            ?? $this->attributes['countryId']
            ?? $this->attributes['country_company_id']
            ?? null;
    }

    public function setCountryIdAttribute($value)
    {
        if ($this->hasTableColumn('country_id')) {
            $this->attributes['country_id'] = $value;
        }
        if ($this->hasTableColumn('countryId')) {
            $this->attributes['countryId'] = $value;
        }
        if ($this->hasTableColumn('country_company_id')) {
            $this->attributes['country_company_id'] = $value;
        }
    }

    public function getCountryCompanyIdAttribute()
    {
        return $this->getCountryIdAttribute();
    }

    public function setCountryCompanyIdAttribute($value)
    {
        $this->setCountryIdAttribute($value);
    }

    public function getLogoUrlAttribute()
    {
        return $this->attributes['logo_url']
            ?? $this->attributes['logoUrl']
            ?? null;
    }

    public function setLogoUrlAttribute($value)
    {
        if ($this->hasTableColumn('logo_url')) {
            $this->attributes['logo_url'] = $value;
        }
        if ($this->hasTableColumn('logoUrl')) {
            $this->attributes['logoUrl'] = $value;
        }
    }

    public function getArchiveLogoIdAttribute()
    {
        return $this->attributes['archive_logo_id']
            ?? $this->attributes['archiveLogoId']
            ?? null;
    }

    public function setArchiveLogoIdAttribute($value)
    {
        if ($this->hasTableColumn('archive_logo_id')) {
            $this->attributes['archive_logo_id'] = $value;
        }
        if ($this->hasTableColumn('archiveLogoId')) {
            $this->attributes['archiveLogoId'] = $value;
        }
    }

    public function getArchiveFooterIdAttribute()
    {
        return $this->attributes['archive_footer_id']
            ?? $this->attributes['archiveFooterId']
            ?? null;
    }

    public function setArchiveFooterIdAttribute($value)
    {
        if ($this->hasTableColumn('archive_footer_id')) {
            $this->attributes['archive_footer_id'] = $value;
        }
        if ($this->hasTableColumn('archiveFooterId')) {
            $this->attributes['archiveFooterId'] = $value;
        }
    }

    public function getArchiveStampIdAttribute()
    {
        return $this->attributes['archive_stamp_id']
            ?? $this->attributes['archiveStampId']
            ?? null;
    }

    public function setArchiveStampIdAttribute($value)
    {
        if ($this->hasTableColumn('archive_stamp_id')) {
            $this->attributes['archive_stamp_id'] = $value;
        }
        if ($this->hasTableColumn('archiveStampId')) {
            $this->attributes['archiveStampId'] = $value;
        }
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function countryCompany()
    {
        return $this->belongsTo(Country::class, $this->hasTableColumn('country_company_id') ? 'country_company_id' : ($this->hasTableColumn('country_id') ? 'country_id' : 'countryId'), 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, $this->hasTableColumn('country_id') ? 'country_id' : ($this->hasTableColumn('countryId') ? 'countryId' : 'country_company_id'), 'id');
    }

    public function archiveFooter()
    {
        return $this->belongsTo(Archive::class, $this->hasTableColumn('archive_footer_id') ? 'archive_footer_id' : 'archiveFooterId', 'id');
    }

    public function archiveLogo()
    {
        return $this->belongsTo(Archive::class, $this->hasTableColumn('archive_logo_id') ? 'archive_logo_id' : 'archiveLogoId', 'id');
    }

    public function archiveStamp()
    {
        return $this->belongsTo(Archive::class, $this->hasTableColumn('archive_stamp_id') ? 'archive_stamp_id' : 'archiveStampId', 'id');
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
