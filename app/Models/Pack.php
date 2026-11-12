<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = 'packs';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'gross_weight' => 'double',
        'net_weight' => 'double',
        'quantity_received' => 'double',
        'qty_packing' => 'integer',
        'version' => 'integer',
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packinglist_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function dealProduct()
    {
        return $this->belongsTo(DealProduct::class, 'deal_product_id');
    }
}
