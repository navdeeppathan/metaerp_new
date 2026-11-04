<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $table = 'packs';
    protected $guarded = [];

    protected $casts = [
        'grossWeight' => 'double',
        'netWeight' => 'double',
        'tareWeight' => 'double',
        'quantity' => 'integer',
    ];

    public function packingList()
    {
        return $this->belongsTo(PackingList::class, 'packing_list_id');
    }

    public function packType()
    {
        return $this->belongsTo(PackType::class, 'pack_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
