<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'code',
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'language_id', 'id');
    }

    public function templates()
    {
        return $this->hasMany(Template::class, 'language_id', 'id');
    }
}
