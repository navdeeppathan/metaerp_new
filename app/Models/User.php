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

    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name'] ?? $this->attributes['name'] ?? null;
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->attributes['photo_url'] ?? null;
    }

    public function setPhotoUrlAttribute($value)
    {
        $this->attributes['photo_url'] = $value;
    }

    public function getSignatureFileAttribute()
    {
        return $this->attributes['signature_file'] ?? null;
    }

    public function setSignatureFileAttribute($value)
    {
        $this->attributes['signature_file'] = $value;
    }

    public function archiveSignature()
    {
        return $this->belongsTo(Archive::class, 'archive_signature_id', 'id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'id_member', 'id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id', 'id');
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_member_ids', 'member_ids', 'contract_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_member_ids', 'member_ids', 'offer_id');
    }
}
