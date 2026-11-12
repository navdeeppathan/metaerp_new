<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelWorkflow extends Model
{
    protected $table = 'models';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
        'version',
    ];

    protected $casts = [
        'active' => 'boolean',
        'version' => 'integer',
    ];

    public function stages()
    {
        return $this->hasMany(Stage::class, 'model_id', 'id')->orderBy('position');
    }
}
