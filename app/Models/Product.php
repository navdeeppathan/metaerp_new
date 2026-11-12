<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'uom',
        'active',
        'base_code_annexix',
        'code',
        'eu_list_of_wastes',
        'hs_code',
        'item_specification',
        'name',
        'symbol',
        'contact_id',
        'version',
        'product_category',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class, 'product_id', 'id');
    }

    public function productContracts()
    {
        return $this->hasMany(ProductContract::class, 'product_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'product_id', 'id');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_product_ids', 'product_ids', 'contact_id');
    }
}
