<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;
    public function account() {
       return $this->belongsTo('App\Models\Accounts','party','id');
    }
    public function saleReturnDescriptions(){
      return $this->hasMany('App\Models\SaleReturnDescription','sale_return_id','id');
    }
    public function saleReturnSundry() {
      return $this->hasMany('App\Models\SaleReturnSundry','sale_return_id','id')
                  ->join('bill_sundrys', 'sale_return_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->select('sale_return_sundries.id','sale_return_sundries.sale_return_id','sale_return_sundries.bill_sundry','sale_return_sundries.amount', 'bill_sundrys.bill_sundry_type','bill_sundrys.adjust_sale_amt');
   }

}
