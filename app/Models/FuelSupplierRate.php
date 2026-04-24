<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelSupplierRate extends Model
{
    use HasFactory;
    protected $table = 'fuel_supplier_rates';

    protected $fillable = [
        'parent_id',
        'account_id',
        'item_id',
        'price',
        'price_date',
        'company_id',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
