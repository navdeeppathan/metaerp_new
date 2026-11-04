<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageAction extends Model
{
    protected $table = 'stageactions';
    protected $guarded = [];

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }
}
