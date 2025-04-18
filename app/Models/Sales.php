<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
   public function saleSundry() {
      return $this->hasMany('App\Models\SaleSundry','sale_id','id')
                  ->join('bill_sundrys','sale_sundries.bill_sundry', '=','bill_sundrys.id')
                  ->select('sale_sundries.id','sale_sundries.sale_id','sale_sundries.bill_sundry','sale_sundries.amount', 'bill_sundrys.bill_sundry_type','bill_sundrys.adjust_sale_amt');
   }     
   public function saleDescription(){
      return $this->hasMany('App\Models\SaleDescription','sale_id','id');
   }
   public function account() {
       return $this->belongsTo('App\Models\Accounts','party','id');
   }
}
