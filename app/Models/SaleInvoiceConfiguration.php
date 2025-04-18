<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInvoiceConfiguration extends Model
{
    use HasFactory;
    protected $fillable = ['company_logo_status','bank_detail_status','bank_name','term_status','company_id'];
    public function terms(){
        return $this->hasMany(SaleInvoiceTermCondition::class,'parent_id','id');
    }
    public function banks(){
        return $this->hasOne(Bank::class,'id','bank_name');
    }
}
