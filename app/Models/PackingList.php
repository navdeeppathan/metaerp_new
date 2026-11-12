<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingList extends Model
{
    protected $table = 'packinglists';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'bill_of_landing_number',
        'date',
        'delivery_note',
        'status',
        'version',
        'contract_id',
        'sinv_number',
    ];

    protected $casts = [
        'active' => 'boolean',
        'date' => 'datetime',
        'version' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'packinglist_id', 'id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'packing_list_id', 'id');
    }
}
