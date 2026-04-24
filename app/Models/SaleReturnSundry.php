<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnSundry extends Model
{
    use HasFactory;
        public function billSundry()
        {
            return $this->belongsTo(
                \App\Models\BillSundrys::class,
                'bill_sundry',
                'id'
            );
        }
}
