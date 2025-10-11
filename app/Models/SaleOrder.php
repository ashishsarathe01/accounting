<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory;

    // Explicitly tell Laravel to use the 'sales' table
    

    protected $fillable = ['sale_order_no','purchase_order_no','purchase_order_date','bill_to','shipp_to','deal_id','freight','company_id','created_by','created_at','status'];
    public function items() {
        return $this->hasMany(SaleOrderItem::class);
    }
    public function billTo() {
        return $this->hasOne(Accounts::class,'id','bill_to');
    }
    public function shippTo() {
        return $this->hasOne(Accounts::class,'id','shipp_to');
    }
    public function orderCreatedBy() {
        return $this->hasOne(User::class,'id','created_by');
    }
}
