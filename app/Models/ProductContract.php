<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductContract extends Model
{
    protected $table = 'productcontracts';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
