<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageActionContractTypeNotAllowed extends Model
{
    protected $table = 'stage_action_contract_types_not_allowed';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'stage_action_id',
        'contract_types_not_allowed',
    ];

    public function stageAction()
    {
        return $this->belongsTo(StageAction::class, 'stage_action_id', 'id');
    }
}
