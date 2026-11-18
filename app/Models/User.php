<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected static ?array $columnsCache = null;

    protected $fillable = [
        'name',
        'username',
        'password',
        'occupation',
        'role',
        'active',
        'status',
        'secret',
        'enable2fa',
        'version',
        'signature',
        'signature_file_name',
        'email',
        // Display Name
        'display_name',
        'displayName',
        // Photo URL
        'photo_url',
        'photoUrl',
        // Signature File
        'signature_file',
        'signatureFile',
        // Archive Signature ID
        'archive_signature_id',
        'archiveSignatureId',
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
        'signatureFile',
    ];

    public function getTableColumns(): array
    {
        if (static::$columnsCache === null) {
            try {
                static::$columnsCache = Schema::getColumnListing($this->getTable());
            } catch (\Throwable $e) {
                static::$columnsCache = [];
            }
        }
        return static::$columnsCache;
    }

    public function hasTableColumn(string $column): bool
    {
        return in_array($column, $this->getTableColumns(), true);
    }

    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name']
            ?? $this->attributes['displayName']
            ?? $this->attributes['name']
            ?? null;
    }

    public function setDisplayNameAttribute($value)
    {
        if ($this->hasTableColumn('display_name')) {
            $this->attributes['display_name'] = $value;
        }
        if ($this->hasTableColumn('displayName')) {
            $this->attributes['displayName'] = $value;
        }
    }

    public function getPhotoUrlAttribute()
    {
        return $this->attributes['photo_url']
            ?? $this->attributes['photoUrl']
            ?? null;
    }

    public function setPhotoUrlAttribute($value)
    {
        if ($this->hasTableColumn('photo_url')) {
            $this->attributes['photo_url'] = $value;
        }
        if ($this->hasTableColumn('photoUrl')) {
            $this->attributes['photoUrl'] = $value;
        }
    }

    public function getSignatureFileAttribute()
    {
        return $this->attributes['signature_file']
            ?? $this->attributes['signatureFile']
            ?? null;
    }

    public function setSignatureFileAttribute($value)
    {
        if ($this->hasTableColumn('signature_file')) {
            $this->attributes['signature_file'] = $value;
        }
        if ($this->hasTableColumn('signatureFile')) {
            $this->attributes['signatureFile'] = $value;
        }
    }

    public function getArchiveSignatureIdAttribute()
    {
        return $this->attributes['archive_signature_id']
            ?? $this->attributes['archiveSignatureId']
            ?? null;
    }

    public function setArchiveSignatureIdAttribute($value)
    {
        if ($this->hasTableColumn('archive_signature_id')) {
            $this->attributes['archive_signature_id'] = $value;
        }
        if ($this->hasTableColumn('archiveSignatureId')) {
            $this->attributes['archiveSignatureId'] = $value;
        }
    }

    public function getStatusAttribute(): string
    {
        return $this->active ? 'ACTIVE' : 'INACTIVE';
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['active'] = in_array(strtolower((string)$value), ['active', '1', 'true', 'on', 'yes'], true) ? 1 : 0;
    }

    public function archiveSignature()
    {
        return $this->belongsTo(Archive::class, $this->hasTableColumn('archive_signature_id') ? 'archive_signature_id' : 'archiveSignatureId', 'id');
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
