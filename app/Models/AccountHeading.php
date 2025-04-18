<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;
class AccountHeading extends Model
{
   use HasFactory;
   public function accountGroup(){
     return $this->hasMany(AccountGroups::class,'heading','id')
                  ->where('delete','=', '0')
                  ->where('status','=', '1')
                  ->whereIn('company_id',[0,Session::get('user_company_id')]);
   }
   public function accountWithHead(){
      return $this->hasMany(Accounts::class,'under_group','id')
                  ->where('delete','=', '0')
                  ->where('status','=', '1')
                  ->where('under_group_type','=', 'head')
                  ->whereIn('company_id',[0,Session::get('user_company_id')]);
   }
}
