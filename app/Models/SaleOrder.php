<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory;

    // Explicitly tell Laravel to use the 'sales' table
    

    protected $fillable = ['sale_order_no','purchase_order_no','purchase_order_date','bill_to','shipp_to','bill_to_address_id','shipp_to_address_id','deal_id','freight','company_id','created_by','created_at','status'];
    public function items() {
        return $this->hasMany(SaleOrderItem::class);
    }
    public function billTo() {
        return $this->hasOne(Accounts::class,'id','bill_to');
    }
    public function shippTo() {
        return $this->hasOne(Accounts::class,'id','shipp_to');
    }
    public function billToOtherAddress() {
        return $this->hasOne(AccountOtherAddress::class,'id','bill_to_address_id');
    }
    public function shippToOtherAddress() {
        return $this->hasOne(AccountOtherAddress::class,'id','shipp_to_address_id');
    }
    public function orderCreatedBy() {
        return $this->hasOne(User::class,'id','created_by');
    }
    public function sale()
    {
        return $this->hasOne(\App\Models\Sales::class, 'sale_order_id', 'id');
    }
    public function createdByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

}
