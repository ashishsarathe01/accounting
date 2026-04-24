<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnDescription extends Model
{
    use HasFactory;
    public function item()
    {
        return $this->belongsTo(
            \App\Models\ManageItems::class,
            'goods_discription',
            'id'
        );
    }
    
    public function unitMaster()
    {
        return $this->belongsTo(
            \App\Models\Units::class,
            'unit',
            'id'
        );
    }
}
