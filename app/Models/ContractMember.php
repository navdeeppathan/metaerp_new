<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractMember extends Model
{
    protected $table = 'contract_member_ids';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'contract_id',
        'member_ids',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_ids', 'id');
    }
}
