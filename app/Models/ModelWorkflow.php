<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelWorkflow extends Model
{
    protected $table = 'models';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function stages()
    {
        return $this->hasMany(Stage::class, 'model_id')->orderBy('priority');
    }
}
