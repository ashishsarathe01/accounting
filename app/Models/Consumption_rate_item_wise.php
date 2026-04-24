<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumption_rate_item_wise extends Model
{
    use HasFactory;
    protected $table = "consumption_rate_item_wise";

    protected $fillable = [
    'item_id',
    'per_kg',
    'variance_rate',
    'status',
    'created_by',
    'updated_by',
    'updated_at',
    'company_id',
];

}
