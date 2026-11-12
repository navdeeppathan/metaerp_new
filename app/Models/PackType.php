<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackType extends Model
{
    protected $table = 'pack_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
