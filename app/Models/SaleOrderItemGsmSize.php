<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrderItemGsmSize extends Model
{
    use HasFactory;
    protected $fillable = ['sale_orders_id','sale_order_item_id','sale_order_item_gsm_id','size','quantity','company_id','created_at'];
}
