<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailItemRate extends Model
{
    use HasFactory;
    protected $table = 'retail_item_rate';

protected $fillable = [
    'retail_rate_change_date_id',
    'item_id',
    'rate',
    'company_id',
    'status',
    'created_by'
];
}
