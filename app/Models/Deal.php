<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table = 'deal';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'dollar_convertion_rate',
        'incoterms',
        'payment_terms',
        'address_id',
        'contact_id',
        'contact_representative_id',
        'currency_id',
        'meta_company_id',
        'meta_representative_id',
        'payment_account_id',
        'payment_terms_type_id',
        'payment_type_id',
        'payment_status',
        'payment_date',
        'shipping_address_id',
        'offer_id',
        'advanced_payment',
        'credit_note_text',
        'debit_note_text',
        'external_order_number',
        'notes',
        'country_of_origin_id',
    ];

    protected $casts = [
        'dollar_convertion_rate' => 'double',
        'advanced_payment' => 'double',
        'payment_date' => 'datetime',
    ];

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'meta_company_id', 'id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function contactRepresentative()
    {
        return $this->belongsTo(Representative::class, 'contact_representative_id', 'id');
    }

    public function metaRepresentative()
    {
        return $this->belongsTo(Representative::class, 'meta_representative_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(BankAccount::class, 'payment_account_id', 'id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    public function paymentTermsType()
    {
        return $this->belongsTo(PaymentTermsType::class, 'payment_terms_type_id', 'id');
    }

    public function countryOfOrigin()
    {
        return $this->belongsTo(Country::class, 'country_of_origin_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function purchaseContract()
    {
        return $this->hasOne(Contract::class, 'purchase_id', 'id');
    }

    public function saleContract()
    {
        return $this->hasOne(Contract::class, 'sale_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'meta_company_id', 'id');
    }

    public function destinationAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(DealProduct::class, 'deal_products', 'deal_id', 'products_id');
    }

    public function dealProducts()
    {
        return $this->belongsToMany(DealProduct::class, 'deal_products', 'deal_id', 'products_id');
    }

    public function getContractAttribute()
    {
        return $this->purchaseContract ?: $this->saleContract;
    }

    public function getDollarConversionRateAttribute()
    {
        return $this->attributes['dollar_convertion_rate'] ?? null;
    }

    public function setDollarConversionRateAttribute($value)
    {
        $this->attributes['dollar_convertion_rate'] = $value;
    }

    public function purchaseOffer()
    {
        return $this->hasOne(Offer::class, 'purchase_id', 'id');
    }

    public function saleOffer()
    {
        return $this->hasOne(Offer::class, 'sale_id', 'id');
    }
}
