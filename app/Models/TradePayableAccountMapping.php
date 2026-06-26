<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradePayableAccountMapping extends Model
{
    use HasFactory;

    protected $table = 'trade_payable_account_mappings';

    protected $fillable = [
        'company_id',
        'account_id',
        'trade_payable_type'
    ];

    public function account()
    {
        return $this->belongsTo(
            Accounts::class,
            'account_id',
            'id'
        );
    }
}