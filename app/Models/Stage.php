<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $table = 'stages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
        'position',
        'type',
        'version',
        'model_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'position' => 'integer',
        'version' => 'integer',
    ];

    public function modelWorkflow()
    {
        return $this->belongsTo(ModelWorkflow::class, 'model_id', 'id');
    }

    public function stageActions()
    {
        return $this->hasMany(StageAction::class, 'stage_id', 'id')->orderBy('priority');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'stage_id', 'id');
    }

    public function getPriorityAttribute()
    {
        return $this->attributes['position'] ?? null;
    }

    public function setPriorityAttribute($value)
    {
        $this->attributes['position'] = $value;
    }
}

