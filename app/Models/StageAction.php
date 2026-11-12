<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageAction extends Model
{
    protected $table = 'stageactions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'priority',
        'trigger_type',
        'action_id',
        'stage_id',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id', 'id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id', 'id');
    }

    public function actionsTriggered()
    {
        return $this->hasMany(Action::class, 'stage_action_id', 'id');
    }

    public function contractTypesNotAllowed()
    {
        return $this->hasMany(StageActionContractTypeNotAllowed::class, 'stage_action_id', 'id');
    }
}
