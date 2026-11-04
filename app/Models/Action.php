<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'actions';
    protected $guarded = [];

    public function stageActions()
    {
        return $this->hasMany(StageAction::class, 'action_id');
    }
}
