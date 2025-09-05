<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPurchaseReport extends Model
{
    use HasFactory;
    public function locationInfo() {
        return $this->belongsTo('App\Models\SupplierLocation','location','id');
    }
    public function headInfo() {
        return $this->belongsTo('App\Models\SupplierSubHead','head_id','id');
    }
}
