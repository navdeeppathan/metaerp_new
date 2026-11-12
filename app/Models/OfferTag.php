<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTag extends Model
{
    protected $table = 'offer_tags';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'offer_id',
        'tags',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }
}
