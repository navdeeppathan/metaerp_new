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

    public function setDataIdAttribute($value)
    {
        $this->attributes['data_id'] = $value;
    }

    public function setDateTimeAttribute($value)
    {
        $this->attributes['date_time'] = $value;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value;
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
