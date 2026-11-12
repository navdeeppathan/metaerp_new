<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function representatives()
    {
        return $this->hasMany(Representative::class, 'department_id', 'id');
    }
}
