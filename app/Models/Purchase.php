<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
   use HasFactory;
   public function purchaseSundry() {
      return $this->hasMany('App\Models\PurchaseSundry','purchase_id','id')
                  ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->select('purchase_sundries.id','purchase_sundries.purchase_id','purchase_sundries.bill_sundry','purchase_sundries.amount', 'bill_sundrys.bill_sundry_type','bill_sundrys.adjust_sale_amt','bill_sundrys.nature_of_sundry');

      //->select(['id', 'purchase_id', 'bill_sundry','amount']);
   }
   public function purchaseDescription(){
      return $this->hasMany('App\Models\PurchaseDescription','purchase_id','id');
   }
   public function account() {
      return $this->belongsTo('App\Models\Accounts','party','id');
   }
   public function purchaseReport(){
      return $this->hasMany('App\Models\SupplierPurchaseReport','purchase_id','id');
   }
}
