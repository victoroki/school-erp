<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    public $table = 'audit_trails';

    public $fillable = [
        'user_id',
        'module',
        'action',
        'record_id',
        'old_values',
        'new_values',
        'ip_address'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($module, $action, $recordId, $old = null, $new = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip()
        ]);
    }
}
