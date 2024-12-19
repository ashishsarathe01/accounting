<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
   use HasFactory;
   public function purchaseSundry() {
      return $this->hasMany('App\Models\PurchaseSundry','purchase_id','id');
   }
   public function purchaseDescription(){
      return $this->hasMany('App\Models\PurchaseDescription','purchase_id','id');
   }
   public function account() {
       return $this->belongsTo('App\Models\Accounts','party','id');
   }
}
