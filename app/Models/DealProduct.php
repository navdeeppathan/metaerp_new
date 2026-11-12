<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealProduct extends Model
{
    protected $table = 'deal_product';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'add_description',
        'description',
        'end_date',
        'eu_list_of_wastes',
        'gross_weight',
        'item_description',
        'item_specification',
        'lme_price',
        'lme_price_percentage',
        'net_weight',
        'packing',
        'premium',
        'price_description',
        'pricing_type',
        'proforma_invoice_number',
        'purchased_currency_rate',
        'quantity',
        'reference_rate',
        'sales_invoice_number',
        'sales_order_number',
        'start_date',
        'total',
        'total_price',
        'unit_price',
        'rate',
        'vat',
        'currency_id',
        'pack_type_id',
        'product_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'quantity' => 'double',
        'unit_price' => 'double',
        'rate' => 'double',
        'vat' => 'double',
        'total' => 'double',
        'total_price' => 'double',
        'gross_weight' => 'double',
        'net_weight' => 'double',
        'lme_price' => 'double',
        'lme_price_percentage' => 'float',
        'premium' => 'double',
        'purchased_currency_rate' => 'double',
        'reference_rate' => 'double',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getTotalPriceAttribute()
    {
        return $this->attributes['total_price'] ?? $this->attributes['totalPrice'] ?? $this->attributes['total'] ?? null;
    }

    public function getTotalAttribute()
    {
        return $this->attributes['total'] ?? $this->attributes['total_price'] ?? $this->attributes['totalPrice'] ?? null;
    }

    public function getUnitPriceAttribute()
    {
        return $this->attributes['unit_price'] ?? $this->attributes['unitPrice'] ?? $this->attributes['rate'] ?? null;
    }

    public function setUnitPriceAttribute($value)
    {
        if (array_key_exists('unitPrice', $this->attributes)) {
            $this->attributes['unitPrice'] = $value;
        } else {
            $this->attributes['unit_price'] = $value;
        }
    }

    public function getRateAttribute()
    {
        return $this->attributes['rate'] ?? $this->attributes['unit_price'] ?? $this->attributes['unitPrice'] ?? null;
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_products', 'products_id', 'deal_id');
    }

    public function deal()
    {
        return $this->belongsToMany(Deal::class, 'deal_products', 'products_id', 'deal_id');
    }

    public function getDealAttribute()
    {
        return $this->deals()->first();
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

    public function packs()
    {
        return $this->hasMany(Pack::class, 'deal_product_id');
    }
}
