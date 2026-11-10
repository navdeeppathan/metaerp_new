<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currency';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'description',
        'name',
        'symbol',
        'encode_html_symbol',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class, 'currency_id');
    }

    public function dealProducts()
    {
        return $this->hasMany(DealProduct::class, 'currency_id');
    }
}
