<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'action',
        'data_id',
        'dataId',
        'date_time',
        'dateTime',
        'history',
        'module',
        'user_id',
        'userId',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    protected static array $tableColumnsCache = [];

    public static function getTableColumns(): array
    {
        $table = (new static)->getTable();
        if (!isset(static::$tableColumnsCache[$table])) {
            try {
                static::$tableColumnsCache[$table] = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                static::$tableColumnsCache[$table] = [];
            }
        }
        return static::$tableColumnsCache[$table];
    }

    public function save(array $options = [])
    {
        $cols = static::getTableColumns();
        if (!empty($cols)) {
            $colFlip = array_flip($cols);

            $dataIdVal = $this->attributes['data_id'] ?? ($this->attributes['dataId'] ?? null);
            if ($dataIdVal !== null) {
                if (isset($colFlip['data_id'])) $this->attributes['data_id'] = $dataIdVal;
                if (isset($colFlip['dataId'])) $this->attributes['dataId'] = $dataIdVal;
            }

            $dateTimeVal = $this->attributes['date_time'] ?? ($this->attributes['dateTime'] ?? null);
            if ($dateTimeVal !== null) {
                if (isset($colFlip['date_time'])) $this->attributes['date_time'] = $dateTimeVal;
                if (isset($colFlip['dateTime'])) $this->attributes['dateTime'] = $dateTimeVal;
            }

            $userIdVal = $this->attributes['user_id'] ?? ($this->attributes['userId'] ?? null);
            if ($userIdVal !== null) {
                if (isset($colFlip['user_id'])) $this->attributes['user_id'] = $userIdVal;
                if (isset($colFlip['userId'])) $this->attributes['userId'] = $userIdVal;
            }

            if (isset($colFlip['created_at']) && !isset($this->attributes['created_at'])) {
                $this->attributes['created_at'] = now();
            }
            if (isset($colFlip['updated_at']) && !isset($this->attributes['updated_at'])) {
                $this->attributes['updated_at'] = now();
            }

            $this->attributes = array_intersect_key($this->attributes, $colFlip);
        }
        return parent::save($options);
    }

    public function getDataIdAttribute()
    {
        return $this->attributes['data_id'] ?? ($this->attributes['dataId'] ?? null);
    }

    public function setDataIdAttribute($value)
    {
        $this->attributes['data_id'] = $value;
        $this->attributes['dataId'] = $value;
    }

    public function getDateTimeAttribute()
    {
        return $this->attributes['date_time'] ?? ($this->attributes['dateTime'] ?? null);
    }

    public function setDateTimeAttribute($value)
    {
        $this->attributes['date_time'] = $value;
        $this->attributes['dateTime'] = $value;
    }

    public function getUserIdAttribute()
    {
        return $this->attributes['user_id'] ?? ($this->attributes['userId'] ?? null);
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value;
        $this->attributes['userId'] = $value;
    }

    public function setActionAttribute($value)
    {
        $map = [
            'CREATE' => 'CREATED',
            'CREATED' => 'CREATED',
            'UPDATE' => 'UPDATED',
            'UPDATED' => 'UPDATED',
            'EDIT' => 'UPDATED',
            'DELETE' => 'DELETED',
            'DELETED' => 'DELETED',
            'CLOSE' => 'CLOSED',
            'CLOSED' => 'CLOSED',
            'CANCEL' => 'CANCELLED',
            'CANCELLED' => 'CANCELLED',
            'CHANGE_STAGE' => 'CHANGE_STAGE',
            'STAGE_TRANSITION' => 'CHANGE_STAGE',
            'TRANSITION' => 'CHANGE_STAGE',
        ];
        $val = strtoupper(trim((string) $value));
        $this->attributes['action'] = $map[$val] ?? 'CREATED';
    }

    public function setModuleAttribute($value)
    {
        $allowed = [
            'USERS', 'COMPANY', 'CONTACT', 'PRODUCT', 'CONTRACT', 'DASHBOARD',
            'ACCESS_CONTROL', 'CONTRACT_CHANGE_STATE', 'LOG', 'SYSTEM_SETTINGS',
            'COUNTRY', 'OFFER', 'GENERATED_FILE', 'TEMPLATE'
        ];
        $val = strtoupper(trim((string) $value));
        $this->attributes['module'] = in_array($val, $allowed) ? $val : 'CONTRACT';
    }

    public function user()
    {
        $cols = static::getTableColumns();
        $foreignKey = (!empty($cols) && in_array('user_id', $cols)) ? 'user_id' : 'userId';
        return $this->belongsTo(User::class, $foreignKey, 'id');
    }
}
