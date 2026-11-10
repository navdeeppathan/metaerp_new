<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
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
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class, 'product_id');
    }
}
