<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $table = 'packinglists';
    public $timestamps = false;
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'packinglist_id');
    }
}
