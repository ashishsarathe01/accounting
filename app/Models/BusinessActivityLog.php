<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class BusinessActivityLog extends Model
{
    protected $table = 'business_activity_logs';

    /**
     * No updated_at column is used
     */
    public const UPDATED_AT = null;

    /**
     * Disable default timestamps
     * (we use action_at instead)
     */
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
        'status',
    ];

    /**
     * Cast JSON data automatically
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    /**
     * COMMON LOGGER FOR RATE CHANGES
     *
     * @param array $data
     * @return void
     */
    public static function logRateChange(array $data): void
    {
        self::create([
            'module_type' => $data['module_type'],     // manage_rate, supplier_bonus, etc.
            'module_id'   => $data['module_id'],       // affected record ID
            'action'      => $data['action'],          // rate_changed
            'old_data'    => json_encode($data['old_data']),
            'new_data'    => isset($data['new_data']) ? json_encode($data['new_data']) : null,
            'action_by'   => $data['action_by'] ?? Auth::id(),
            'company_id'  => $data['company_id'],
            'status'      => $data['status'] ?? 1,
            'action_at'   => now(),
        ]);
    }
}
