<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;
    public function account() {
       return $this->belongsTo('App\Models\Accounts','party','id');
    }
    public function purchaseReturnDescription(){
      return $this->hasMany('App\Models\PurchaseReturnDescription','purchase_return_id','id');
    }
    public function purchaseReturnSundry() {
      return $this->hasMany('App\Models\PurchaseReturnSundry','purchase_return_id','id')
                  ->join('bill_sundrys', 'purchase_return_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->select('purchase_return_sundries.id','purchase_return_sundries.purchase_return_id','purchase_return_sundries.bill_sundry','purchase_return_sundries.amount', 'bill_sundrys.bill_sundry_type','bill_sundrys.adjust_sale_amt');
   }
}
