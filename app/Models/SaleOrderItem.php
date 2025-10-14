<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;
class SaleOrderItem extends Model
{
    use HasFactory;
    protected $fillable = ['sale_order_id','item_id','price','bill_price','unit','sub_unit','company_id','created_at','status'];
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
        public function SaleOrderSettingUnitMaster()
        {
            return $this->belongsTo(SaleOrderSetting::class,'unit','item_id')
                        ->where('setting_type','UNIT')
                        ->where('company_id',Session::get('user_company_id'));
        }
        
}
