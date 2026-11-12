<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    public $timestamps = false;
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_member');
    }
}
