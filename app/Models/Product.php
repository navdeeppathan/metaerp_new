<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_products', 'product_id', 'contact_id');
    }
}
