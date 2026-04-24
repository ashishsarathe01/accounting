<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'module_type',
        'module_id',
        'action',
        'old_data',
        'new_data',
        'action_by',
        'company_id',
        'action_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
}
