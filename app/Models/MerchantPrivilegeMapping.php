<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantPrivilegeMapping extends Model
{
    protected $table = 'merchant_privilege_mappings';

    protected $fillable = [
        'module_id',
        'merchant_id',
        'company_id',
        'status'
    ];

    public $timestamps = true;
}
