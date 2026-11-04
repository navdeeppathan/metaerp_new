<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offer';
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'double',
        'unitPrice' => 'double',
        'total' => 'double',
        'validUntil' => 'datetime',
    ];

    public function metaCompany()
    {
        return $this->belongsTo(Company::class, 'meta_company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
