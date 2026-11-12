<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactProduct extends Model
{
    protected $table = 'contact_product_ids';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contact_id',
        'product_ids',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_ids', 'id');
    }
}
