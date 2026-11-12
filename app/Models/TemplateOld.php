<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateOld extends Model
{
    protected $table = 'templates_old';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content',
        'country_id',
        'description',
        'name',
        'not_required_keys',
        'type',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
