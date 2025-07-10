<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDescription extends Model
{
    use HasFactory;
    public function item() {
        return $this->belongsTo('App\Models\ManageItems','goods_discription','id');
    }
    public function units() {
        return $this->belongsTo('App\Models\Units','unit','id');
    }
    public function parameterColumnInfo() {
        return $this->hasMany('App\Models\PurchaseParameterInfo','purchase_desc_row_id','id');
    }
}
