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
        'quality',
        'quantity',
        'quantity_collected',
        'quantity_received',
        'quantity_shipped',
        'rate',
        'start_date',
        'status',
        'total_price',
        'uom',
        'version',
        'product_id',
        'associated_contract_id',
        'contract_order_code',
    ];

    protected $casts = [
        'active' => 'boolean',
        'quantity' => 'double',
        'total_price' => 'double',
        'rate' => 'double',
        'premium' => 'double',
        'lme_price' => 'double',
        'lme_price_percentage' => 'float',
        'net_weight' => 'double',
        'gross_weight' => 'double',
        'quantity_collected' => 'double',
        'quantity_received' => 'double',
        'quantity_shipped' => 'double',
        'version' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getTotalPriceAttribute()
    {
        return $this->attributes['total_price'] ?? null;
    }

    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = $value;
    }

    public function getTotalAttribute()
    {
        return $this->attributes['total_price'] ?? null;
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total_price'] = $value;
    }

    public function getUnitPriceAttribute()
    {
        return $this->attributes['rate'] ?? null;
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['rate'] = $value;
    }

    public function getRateAttribute()
    {
        return $this->attributes['rate'] ?? null;
    }

    public function setRateAttribute($value)
    {
        $this->attributes['rate'] = $value;
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

    public function getCurrencyAttribute()
    {
        return $this->deals->first()?->currency;
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'deal_product_id');
    }
}
