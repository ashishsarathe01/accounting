<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','business_type','company_name','gst_applicable','email_id','mobile_no','pan',
     // SMTP Fields
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_name',];

    /**
     * Encrypt SMTP Password before saving
     */
    public function setSmtpPasswordAttribute($value)
    {
        $this->attributes['smtp_password'] = $value ? encrypt($value) : null;
    }

    /**
     * Decrypt SMTP Password when fetching
     */
    public function getSmtpPasswordAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }




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
