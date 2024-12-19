<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
   public function saleSundry() {
      return $this->hasMany('App\Models\SaleSundry','sale_id','id');
   }
   public function saleDescription(){
      return $this->hasMany('App\Models\SaleDescription','sale_id','id');
   }
   public function account() {
       return $this->belongsTo('App\Models\Accounts','party','id');
   }
}
