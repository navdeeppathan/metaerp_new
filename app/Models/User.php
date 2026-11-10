<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'display_name',
        'name',
        'occupation',
        'password',
        'photo_url',
        'role',
        'signature_file',
        'username',
        'version',
        'signature',
        'enable2fa',
        'secret',
        'signature_file_name',
        'archive_signature_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'enable2fa' => 'boolean',
        'version' => 'integer',
    ];

    protected $hidden = [
        'password',
        'secret',
        'signature_file',
    ];

    public function getEmailAttribute()
    {
        return $this->attributes['username'] ?? null;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name'] ?? $this->attributes['name'] ?? null;
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'id_member');
    }
}
