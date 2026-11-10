<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Representative extends Model
{
    protected $table = 'representatives';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'email',
        'name',
        'phone',
        'type',
        'job_title',
        'mobile',
        'phone2',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'addresses_representatives', 'representatives_id', 'address_id');
    }
}
