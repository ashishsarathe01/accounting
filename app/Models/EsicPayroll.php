<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsicPayroll extends Model
{
    protected $table = 'esic_payrolls';

    protected $fillable = [
        'company_id',
        'employee_user_id',
        'month_year',
        'gross_salary',
        'absent',
        'reason_code',
        'last_working_day',
    ];
}