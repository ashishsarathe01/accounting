<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitLossGroupMapping extends Model
{
    protected $table = 'profit_loss_group_mapping';

    protected $fillable = [
        'company_id',
        'group_id',
        'record_type',
        'mapping_name'
    ];
}