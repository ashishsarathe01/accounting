<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Session;
class AccountGroups extends Model
{
   use HasFactory;
   public function account(){
      return $this->hasMany(Accounts::class,'under_group','id')
                  ->where('delete','=', '0')
                  ->where('status','=', '1')
                  ->where('under_group_type','=', 'group')
                  ->whereIn('company_id',[0,Session::get('user_company_id')]);
   }
   
}
