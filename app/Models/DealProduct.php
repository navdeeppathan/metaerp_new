<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealProduct extends Model
{
    protected $table = 'deal_product';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
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
        'gross_weight' => 'double',
        'net_weight' => 'double',
        'quantity' => 'double',
        'quantity_collected' => 'double',
        'quantity_received' => 'double',
        'quantity_shipped' => 'double',
        'rate' => 'double',
        'total_price' => 'double',
        'premium' => 'double',
        'lme_price' => 'double',
        'lme_price_percentage' => 'float',
        'version' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function associatedContract()
    {
        return $this->belongsTo(Contract::class, 'associated_contract_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'deal_product_id', 'id');
    }

    public function detachedNoteDetails()
    {
        return $this->hasMany(DetachedNoteDetail::class, 'deal_product_id', 'id');
    }
}
