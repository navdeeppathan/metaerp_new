<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealProduct extends Model
{
    protected $table = 'dealproduct';
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'double',
        'unitPrice' => 'double',
        'vat' => 'double',
        'total' => 'double',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function packType()
    {
        return $this->belongsTo(PackType::class, 'pack_type_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
