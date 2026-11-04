<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table = 'deal';
    protected $guarded = [];

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'meta_company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function contactRepresentative()
    {
        return $this->belongsTo(Representative::class, 'contact_representative_id');
    }

    public function metaRepresentative()
    {
        return $this->belongsTo(Representative::class, 'meta_representative_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function destinationAddress()
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function paymentTermsType()
    {
        return $this->belongsTo(PaymentTermsType::class, 'payment_terms_type_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function products()
    {
        return $this->hasMany(DealProduct::class, 'deal_id');
    }
}
