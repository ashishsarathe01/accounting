<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetailRateChangeDate extends Model
{
    use HasFactory;

    protected $table = 'retail_rate_change_date';

protected $fillable = [
    'date','time','company_id','created_by','status'
];
}
