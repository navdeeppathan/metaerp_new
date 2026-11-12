<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'types';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_type_ids', 'type_ids', 'contact_id');
    }
}

