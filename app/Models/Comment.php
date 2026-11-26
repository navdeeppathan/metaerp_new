<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_member',
        'user_id',
        'message',
        'time',
        'version',
        'contract_id',
        'offer_id',
    ];

    protected $casts = [
        'time' => 'datetime',
        'version' => 'integer',
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

            // Synchronize id_member and user_id
            $memberVal = $this->attributes['id_member'] ?? ($this->attributes['user_id'] ?? null);
            if ($memberVal !== null) {
                if (isset($colFlip['id_member'])) $this->attributes['id_member'] = $memberVal;
                if (isset($colFlip['user_id'])) $this->attributes['user_id'] = $memberVal;
            }

            // Synchronize time and created_at
            $timeVal = $this->attributes['time'] ?? ($this->attributes['created_at'] ?? null);
            if ($timeVal !== null) {
                if (isset($colFlip['time'])) $this->attributes['time'] = $timeVal;
                if (isset($colFlip['created_at'])) $this->attributes['created_at'] = $timeVal;
            }

            if (isset($colFlip['updated_at']) && !isset($this->attributes['updated_at'])) {
                $this->attributes['updated_at'] = now();
            }

            $this->attributes = array_intersect_key($this->attributes, $colFlip);
        }
        return parent::save($options);
    }

    public function getTimeAttribute()
    {
        return $this->attributes['time'] ?? ($this->attributes['created_at'] ?? null);
    }

    public function setTimeAttribute($value)
    {
        $this->attributes['time'] = $value;
        $this->attributes['created_at'] = $value;
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function member()
    {
        $cols = static::getTableColumns();
        $foreignKey = (!empty($cols) && in_array('id_member', $cols)) ? 'id_member' : 'user_id';
        return $this->belongsTo(User::class, $foreignKey, 'id');
    }

    public function user()
    {
        $cols = static::getTableColumns();
        $foreignKey = (!empty($cols) && in_array('id_member', $cols)) ? 'id_member' : 'user_id';
        return $this->belongsTo(User::class, $foreignKey, 'id');
    }
}
