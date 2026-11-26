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
        $fk = \Illuminate\Support\Facades\Schema::hasColumn('packs', 'packing_list_id') ? 'packing_list_id' : 'packinglist_id';
        return $this->belongsTo(PackingList::class, $fk, 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function packType()
    {
        return $this->belongsTo(PackType::class, 'pack_type_id', 'id');
    }

    public function dealProduct()
    {
        return $this->belongsTo(DealProduct::class, 'deal_product_id', 'id');
    }
}
