<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrderItemGsm extends Model
{
    use HasFactory;
    protected $fillable = ['sale_orders_id','sale_order_item_id','gsm','company_id','created_at','status'];
    public function details() {
        return $this->hasMany(SaleOrderItemGsmSize::class);
    }
}
