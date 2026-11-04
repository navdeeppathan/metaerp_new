<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $table = 'stages';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'priority' => 'integer'];

    public function modelWorkflow()
    {
        return $this->belongsTo(ModelWorkflow::class, 'model_id');
    }

    public function stageActions()
    {
        return $this->hasMany(StageAction::class, 'stage_id')->orderBy('priority');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'stage_id');
    }
}
