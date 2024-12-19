<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','business_type','company_name','gst_applicable','email_id','mobile_no','pan'];


    public function companyOwnerdelted()
   {
      return $this->hasMany(Owner::class, 'company_id')->where('deleted','=', 1);
   }
   public function companyOwner()
   {
      return $this->hasMany(Owner::class, 'company_id')->where('deleted','=', 0);
   }

   public function companyShareholder()
   {
      return $this->hasMany(Shareholder::class, 'company_id');
   }
   public function companySharetransfer()
   {
      return $this->hasMany(ShareTransfer::class, 'company_id');
   }
   public function companyBank()
   {
      return $this->hasMany(Bank::class, 'company_id');
   }
}
