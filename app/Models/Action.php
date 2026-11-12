<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'actions';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'template_type',
        'validation_type',
        'template_id',
        'stage_action_id',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    public function stageAction()
    {
        return $this->belongsTo(StageAction::class, 'stage_action_id', 'id');
    }

    public function stageActions()
    {
        return $this->hasMany(StageAction::class, 'action_id', 'id');
    }
}
