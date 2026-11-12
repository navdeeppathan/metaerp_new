<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content',
        'country_id',
        'description',
        'name',
        'not_required_keys',
        'type',
        'language_id',
        'original_content',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function actions()
    {
        return $this->hasMany(Action::class, 'template_id', 'id');
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class, 'template_id', 'id');
    }
}
