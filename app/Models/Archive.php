<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $table = 'archive';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'content',
    ];

    public function footerCompanies()
    {
        return $this->hasMany(Company::class, 'archive_footer_id', 'id');
    }

    public function logoCompanies()
    {
        return $this->hasMany(Company::class, 'archive_logo_id', 'id');
    }

    public function stampCompanies()
    {
        return $this->hasMany(Company::class, 'archive_stamp_id', 'id');
    }

    public function signatureUsers()
    {
        return $this->hasMany(User::class, 'archive_signature_id', 'id');
    }
}
