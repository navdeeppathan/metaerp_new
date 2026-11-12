<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_member',
        'message',
        'time',
        'version',
        'contract_id',
        'offer_id',
    ];

    protected $casts = [
        'time' => 'datetime',
        'version' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'id_member', 'id');
    }
}
