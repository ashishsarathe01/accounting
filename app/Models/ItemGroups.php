<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemGroups extends Model
{
    protected $table = 'item_groups';

    protected $fillable = [
        'company_id',
        'group_name',
        'parameterized_stock_status',
        'config_status',
        'no_of_parameter',
        'alternative_qty',
        'status',
        'delete',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function items(): HasMany
    {
        // g_name in manage_items = item_groups.id
        return $this->hasMany(ManageItems::class, 'g_name', 'id');
    }
}
