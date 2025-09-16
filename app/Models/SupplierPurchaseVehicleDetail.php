<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPurchaseVehicleDetail extends Model
{
    use HasFactory;
    public function purchaseReport(){
        return $this->hasMany('App\Models\SupplierPurchaseReport','purchase_id','id');
    }
    public function locationInfo() {
        return $this->belongsTo('App\Models\SupplierLocation','location','id');
    }
    public function headInfo() {
        return $this->belongsTo('App\Models\SupplierSubHead','head_id','id');
    }
    public function accountInfo() {
        return $this->belongsTo('App\Models\Accounts','account_id','id');
    }
    public function purchaseInfo() {
        return $this->belongsTo('App\Models\Purchase','map_purchase_id','id');
    }
}
