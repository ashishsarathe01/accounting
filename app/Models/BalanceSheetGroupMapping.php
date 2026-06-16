<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetGroupMapping extends Model
{
    protected $table = 'balance_sheet_group_mapping';

    protected $fillable = [
        'company_id',
        'record_type',
        'group_id',
        'trade_payable_type',
        'mapping_name'
    ];
}