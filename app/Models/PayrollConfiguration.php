<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollConfiguration extends Model
{
    protected $table = 'payroll_configurations';

    protected $fillable = [
        'type',
        'account_id',
        'company_id'
    ];
}