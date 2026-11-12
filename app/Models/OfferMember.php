<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferMember extends Model
{
    protected $table = 'offer_member_ids';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'offer_id',
        'member_ids',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_ids', 'id');
    }
}
