<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManageItems extends Model
{
    protected $table = 'manage_items';

    protected $fillable = [
        'company_id',
        'name',
        'p_name',
        'u_name',
        'hsn_code',
        'gst_rate',
        'item_type',
        'opening_balance_qty',
        'opening_balance_qt_type',
        'opening_balance',
        'opening_balance_type',
        'g_name',
        'parameterized_status',
        'section',
        'rate_of_tcs',
        'status',
        'delete',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ItemGroups::class, 'g_name', 'id');
    }
}
