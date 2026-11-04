<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $table = 'packinglists';
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'packing_list_id');
    }
}
