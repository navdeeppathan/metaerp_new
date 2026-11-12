<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = 'packs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'container_number',
        'gross_weight',
        'net_weight',
        'packing_type',
        'qty_packing',
        'version',
        'packinglist_id',
        'product_id',
        'quantity_received',
        'deal_product_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'gross_weight' => 'double',
        'net_weight' => 'double',
        'qty_packing' => 'double',
        'quantity_received' => 'double',
        'version' => 'integer',
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packinglist_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function dealProduct()
    {
        return $this->belongsTo(DealProduct::class, 'deal_product_id', 'id');
    }
}
