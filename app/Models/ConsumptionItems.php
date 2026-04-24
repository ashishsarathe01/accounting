<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionItems extends Model
{
    use HasFactory;
    protected $table = "consumption_items";

    protected $fillable = [
    'compsumption_item_wise_rate_id',
    'item_id',
    'consumption_rate',
    'status',
    'company_id',
];

}
