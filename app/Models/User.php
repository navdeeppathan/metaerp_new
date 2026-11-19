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
        'version' => 'integer',
    ];

    protected $hidden = [
        'password',
        'secret',
        'signature_file',
        'signatureFile',
    ];

    private function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_null($value)) {
            return false;
        }
        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }
        if (is_string($value)) {
            if ($value === "\x01" || $value === "\x00" || $value === "\0" || $value === chr(1) || $value === chr(0)) {
                return ord($value) === 1;
            }
            $trimmed = strtolower(trim($value));
            return in_array($trimmed, ['1', 'true', 'active', 'on', 'yes'], true);
        }
        return (bool) $value;
    }

    public function getActiveAttribute($value): bool
    {
        return $this->parseBooleanValue($value ?? ($this->attributes['active'] ?? null));
    }

    public function setActiveAttribute($value): void
    {
        $this->attributes['active'] = $this->parseBooleanValue($value) ? 1 : 0;
    }

    public function getEnable2faAttribute($value): bool
    {
        return $this->parseBooleanValue($value ?? ($this->attributes['enable2fa'] ?? null));
    }

    public function setEnable2faAttribute($value): void
    {
        $this->attributes['enable2fa'] = $this->parseBooleanValue($value) ? 1 : 0;
    }

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
        $this->setActiveAttribute($value);
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

    private ?array $_resolvedPermissions = null;

    /**
     * Determine if user is super administrator with unrestricted access.
     */
    public function isSuperAdmin(): bool
    {
        if ((int)$this->id === 1) {
            return true;
        }

        $username = strtolower(trim((string)$this->username));
        if (in_array($username, ['admin', 'root@root.com', 'root'], true)) {
            return true;
        }

        $role = strtoupper(trim((string)$this->role));
        if (in_array($role, ['ADMIN', 'ROLE_ADMIN', 'SUPER ADMIN', 'SUPERADMIN'], true)) {
            return true;
        }

        // Check if user has an access record with wildcard module
        $perms = $this->getResolvedPermissions();
        foreach ($perms as $p) {
            if ($p['module'] === '*' && in_array($p['access_type'], ['FULL', 'ALL'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get array of all resolved permission grants for this user.
     */
    public function getResolvedPermissions(): array
    {
        if ($this->_resolvedPermissions !== null) {
            return $this->_resolvedPermissions;
        }

        $list = [];
        try {
            $permissions = $this->relationLoaded('permissions')
                ? $this->permissions
                : $this->permissions()->with(['accessGroup.accessList'])->get();

            foreach ($permissions as $p) {
                $group = $p->accessGroup;
                if (!$group) {
                    continue;
                }
                // Check if group is active if active column exists
                if (isset($group->active) && $group->active !== null && !$group->active) {
                    continue;
                }

                $accesses = $group->relationLoaded('accessList')
                    ? $group->accessList
                    : $group->accessList()->get();

                foreach ($accesses as $acc) {
                    if (isset($acc->active) && $acc->active !== null && !$acc->active) {
                        continue;
                    }

                    $module = strtoupper(trim((string)$acc->module));
                    $type = strtoupper(trim((string)($acc->access_type ?? $acc->accessType ?? '')));

                    $list[] = [
                        'module' => $module,
                        'access_type' => $type,
                        'company_id' => $p->company_id,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $list = [];
        }

        $this->_resolvedPermissions = $list;
        return $this->_resolvedPermissions;
    }

    /**
     * Check whether user has permission for given module(s) and access type.
     * Matches exact React logic from FuseUtils.hasPermission.
     *
     * @param string|array $modules Module name(s) e.g. 'USERS', 'PRODUCT', 'CONTRACT', 'SYSTEM_SETTINGS'
     * @param string|null $accessType 'FULL' or 'READ' (or null for any access)
     * @param int|null $companyId Optional company ID scoping
     * @return bool
     */
    public function hasPermission($modules, ?string $accessType = null, $companyId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (empty($modules)) {
            return true;
        }

        if (!is_array($modules)) {
            $modules = [$modules];
        }

        $modules = array_map(fn($m) => strtoupper(trim((string)$m)), $modules);

        // Wildcard module allows all authenticated users
        if (in_array('*', $modules, true)) {
            return true;
        }

        $permissions = $this->getResolvedPermissions();
        if (empty($permissions)) {
            return false;
        }

        $requiredType = $accessType ? strtoupper(trim($accessType)) : null;

        foreach ($modules as $searchModule) {
            foreach ($permissions as $perm) {
                // If companyId is specified, ensure it matches
                if ($companyId !== null && !empty($perm['company_id']) && (int)$perm['company_id'] !== (int)$companyId) {
                    continue;
                }

                if ($perm['module'] === $searchModule || $perm['module'] === '*') {
                    if ($requiredType === 'FULL') {
                        // FULL access or individual write actions
                        if (in_array($perm['access_type'], ['FULL', 'CREATE', 'UPDATE', 'DELETE', 'ALL'], true)) {
                            return true;
                        }
                    } else {
                        // Any access allows READ
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Shorthand to check if user has FULL (write/create/edit/delete) permission.
     */
    public function hasFullPermission($modules, $companyId = null): bool
    {
        return $this->hasPermission($modules, 'FULL', $companyId);
    }

    /**
     * Return list of company IDs the user has access to for a given module.
     * Matches FuseUtils.getCompaniesPermission.
     */
    public function getAccessibleCompanies(string $module, ?string $accessType = null): array
    {
        if ($this->isSuperAdmin()) {
            return Company::pluck('id')->toArray();
        }

        $module = strtoupper(trim($module));
        $requiredType = $accessType ? strtoupper(trim($accessType)) : null;
        $permissions = $this->getResolvedPermissions();

        $companyIds = [];
        foreach ($permissions as $p) {
            if ($p['module'] === $module || $p['module'] === '*') {
                if ($requiredType === 'FULL') {
                    if (in_array($p['access_type'], ['FULL', 'CREATE', 'UPDATE', 'DELETE', 'ALL'], true)) {
                        if (!empty($p['company_id'])) {
                            $companyIds[] = (int)$p['company_id'];
                        }
                    }
                } else {
                    if (!empty($p['company_id'])) {
                        $companyIds[] = (int)$p['company_id'];
                    }
                }
            }
        }

        return array_values(array_unique($companyIds));
    }
}
