<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
    'month_year',
    'employee_user_id',
    'branch',
    'salary',
    'absent',
    'basic_salary',
    'dearness_allowance',
    'incentive',
    'gross_salary',
    'tds',
    'esi',
    'pf',
    'lwf',
    'other_deductions',
    'net_payment',
    'journal_id',
    'company_id',
    'created_by'
];

}
