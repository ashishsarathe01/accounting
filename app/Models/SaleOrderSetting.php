<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleOrderSetting extends Model
{
    protected $table = 'sale-order-settings'; // matches your SQL

    protected $fillable = [
        'item_id',
        'setting_type',
        'setting_for',
        'company_id',
        'status',
    ];

    public $timestamps = true; // so created_at/updated_at auto-fill
}
