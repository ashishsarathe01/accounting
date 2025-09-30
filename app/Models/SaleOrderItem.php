<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrderItem extends Model
{
    use HasFactory;
    protected $fillable = ['sale_order_id','item_id','price','bill_price','unit','sub_unit','company_id','created_at'];
    public function gsms() {
        return $this->hasMany(SaleOrderItemGsm::class);
    }
    public function item()
        {
            return $this->belongsTo(ManageItems::class, 'item_id', 'id');
        }

        public function unitMaster()
        {
            return $this->belongsTo(Units::class, 'unit', 'id');
        }
}
